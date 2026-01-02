/**
 * WooCommerce Cart Reminder Timer - Frontend Countdown
 */

;((jQuery) => {
  let mounted = false
  let remaining = 0
  let timerInterval = null
  let isDismissed = false
  let totalDuration = 0
  let timerExpired = false

  /**
   * Format seconds to MM:SS format
   */
  function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60)
    const secs = seconds % 60
    return minutes + ":" + (secs < 10 ? "0" : "") + secs
  }

  /**
   * Mount timer into DOM
   */
  function mountTimer() {
    if (mounted) {
      return
    }

    if (!window.WCRT_DATA) {
      return
    }

    const container = getCartCheckoutContainer()

    if (!container) {
      return
    }

    if (container.querySelector(".wcrt-timer")) {
      return
    }

    const timerElement = createTimerElement()
    if (window.WCRT_DATA.position === "top") {
      container.prepend(timerElement)
    } else {
      container.appendChild(timerElement)
    }

    const progressBar = timerElement.querySelector(".wcrt-progress-bar")
    const timeSpan = timerElement.querySelector(".wcrt-time")

    startCountdown(timeSpan, timerElement, progressBar)
    mounted = true
  }

  /**
   * Get cart or checkout container
   */
  function getCartCheckoutContainer() {
    // Cart page containers
    if (window.WCRT_DATA.show_on === "cart" || window.WCRT_DATA.show_on === "both") {
      const cartContainers = [
        ".woocommerce-cart-form",
        ".wc-block-cart",
        ".woo-next-cart",
        '[data-testid="cart-form"]',
        ".cart-wrapper",
      ]

      for (const selector of cartContainers) {
        const element = document.querySelector(selector)
        if (element) {
          return element
        }
      }
    }

    // Checkout page containers
    if (window.WCRT_DATA.show_on === "checkout" || window.WCRT_DATA.show_on === "both") {
      const checkoutContainers = [
        ".wc-block-checkout",
        ".checkout",
        '[data-testid="checkout"]',
        ".woocommerce-checkout",
        "form.checkout",
        ".woo-next-checkout",
        '[data-testid="checkout-form"]',
        ".checkout-form",
      ]

      for (const selector of checkoutContainers) {
        const element = document.querySelector(selector)
        if (element) {
          return element
        }
      }
    }

    return null
  }

  /**
   * Create timer HTML element
   */
  function createTimerElement() {
    const div = document.createElement("div")
    div.className = "wcrt-timer " + window.WCRT_DATA.color_scheme

    const html = `
			${window.WCRT_DATA.show_progress ? '<div class="wcrt-progress-container"><div class="wcrt-progress-bar"></div></div>' : ""}
			<div class="wcrt-content">
				<span class="wcrt-message">⏳ ${window.WCRT_DATA.messages[window.WCRT_DATA.variant][window.WCRT_DATA.loggedIn ? "user" : "guest"]}</span>
				<strong class="wcrt-timer-value"><span class="wcrt-time">00:00</span></strong>
				${window.WCRT_DATA.dismissable ? '<button type="button" class="wcrt-dismiss-btn">✕ Dismiss</button>' : ""}
			</div>
		`

    div.innerHTML = html

    // Attach dismiss handler
    if (window.WCRT_DATA.dismissable) {
      const dismissBtn = div.querySelector(".wcrt-dismiss-btn")
      dismissBtn.addEventListener("click", (e) => {
        e.preventDefault()
        isDismissed = true
        div.style.display = "none"
        createReopenButton()
        localStorage.setItem("wcrt_dismissed", "1")
      })
    }

    return div
  }

  /**
   * Start countdown timer
   */
  function startCountdown(targetSpan, timerDiv, progressBar) {
    function tick() {
      if (remaining <= 0) {
        timerExpired = true
        timerDiv.innerHTML = "⚠️ " + window.WCRT_DATA.expiredMessage
        timerDiv.classList.add("expired")
        clearInterval(timerInterval)

        // Remove coupon via AJAX
        jQuery.post(window.WCRT_DATA.ajax_url, {
          action: "wcrt_remove_expired_coupon",
          nonce: window.WCRT_DATA.nonce,
        })

        // Update page to reflect coupon removal
        jQuery("body").trigger("wc_update_cart")

        if (localStorage.getItem("wcrt_dismissed")) {
          localStorage.removeItem("wcrt_dismissed")
          const reopenBtn = document.querySelector(".wcrt-reopen-btn")
          if (reopenBtn) {
            reopenBtn.style.display = "none"
          }
        }
        return
      }

      targetSpan.textContent = formatTime(remaining)

      if (progressBar) {
        const progress = (remaining / totalDuration) * 100
        progressBar.style.width = progress + "%"
      }

      // Add critical class when 1 minute left
      if (remaining === 60 && window.WCRT_DATA.enable_sound) {
        playAlertSound()
        timerDiv.classList.add("critical")
      }

      remaining--
      timerInterval = setTimeout(tick, 1000)
    }

    tick()
  }

  /**
   * Create reopen button for dismissed timer
   */
  function createReopenButton() {
    let reopenBtn = document.querySelector(".wcrt-reopen-btn")

    if (reopenBtn) {
      reopenBtn.classList.add("show")
      return
    }

    reopenBtn = document.createElement("button")
    reopenBtn.className = "wcrt-reopen-btn show"
    reopenBtn.innerHTML = "⏳"
    reopenBtn.type = "button"

    reopenBtn.addEventListener("click", function (e) {
      e.preventDefault()
      isDismissed = false
      const timer = document.querySelector(".wcrt-timer")
      if (timer) {
        timer.style.display = "block"
        this.classList.remove("show")
        setTimeout(() => {
          reopenBtn.remove()
        }, 400)
      }
      localStorage.removeItem("wcrt_dismissed")
    })

    document.body.appendChild(reopenBtn)
  }

  /**
   * Play alert sound using Web Audio API
   */
  function playAlertSound() {
    try {
      const audioContext = new (window.AudioContext || window.webkitAudioContext)()
      const oscillator = audioContext.createOscillator()
      const gainNode = audioContext.createGain()

      oscillator.connect(gainNode)
      gainNode.connect(audioContext.destination)

      oscillator.frequency.value = 800
      oscillator.type = "sine"

      gainNode.gain.setValueAtTime(0.3, audioContext.currentTime)
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5)

      oscillator.start(audioContext.currentTime)
      oscillator.stop(audioContext.currentTime + 0.5)
    } catch (e) {
      // Silent fail if Web Audio API not available
    }
  }

  /**
   * Initialize on document ready
   */
  jQuery(() => {
    if (window.WCRT_DATA) {
      remaining = window.WCRT_DATA.remaining
      totalDuration = window.WCRT_DATA.duration

      if (localStorage.getItem("wcrt_dismissed")) {
        isDismissed = true
      }

      mountTimer()
    }
  })

  /**
   * Remount timer on cart update
   */
  jQuery(document.body).on("updated_cart_totals wc_fragments_loaded added_to_cart removed_from_cart", () => {
    if (!isDismissed && window.WCRT_DATA) {
      mounted = false
      clearInterval(timerInterval)
      remaining = window.WCRT_DATA.remaining
      totalDuration = window.WCRT_DATA.duration
      timerExpired = false
      mountTimer()
    }
  })
})(window.jQuery || window.jQuery.noConflict())
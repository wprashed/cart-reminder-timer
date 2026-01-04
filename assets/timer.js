/**
 * Cart Reminder Timer - Frontend Countdown
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

    if (!window.CRT_DATA) {
      return
    }

    const container = getCartCheckoutContainer()

    if (!container) {
      return
    }

    if (container.querySelector(".crt-timer")) {
      return
    }

    const timerElement = createTimerElement()
    if (window.CRT_DATA.position === "top") {
      container.prepend(timerElement)
    } else {
      container.appendChild(timerElement)
    }

    const progressBar = timerElement.querySelector(".crt-progress-bar")
    const timeSpan = timerElement.querySelector(".crt-time")

    startCountdown(timeSpan, timerElement, progressBar)
    mounted = true
  }

  /**
   * Get cart or checkout container
   */
  function getCartCheckoutContainer() {
    // Cart page containers
    if (window.CRT_DATA.show_on === "cart" || window.CRT_DATA.show_on === "both") {
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
    if (window.CRT_DATA.show_on === "checkout" || window.CRT_DATA.show_on === "both") {
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
    div.className = "crt-timer " + window.CRT_DATA.color_scheme

    const html = `
			${window.CRT_DATA.show_progress ? '<div class="crt-progress-container"><div class="crt-progress-bar"></div></div>' : ""}
			<div class="crt-content">
				<span class="crt-message">⏳ ${window.CRT_DATA.messages[window.CRT_DATA.variant][window.CRT_DATA.loggedIn ? "user" : "guest"]}</span>
				<strong class="crt-timer-value"><span class="crt-time">00:00</span></strong>
				${window.CRT_DATA.dismissable ? '<button type="button" class="crt-dismiss-btn">✕ Dismiss</button>' : ""}
			</div>
		`

    div.innerHTML = html

    // Attach dismiss handler
    if (window.CRT_DATA.dismissable) {
      const dismissBtn = div.querySelector(".crt-dismiss-btn")
      dismissBtn.addEventListener("click", (e) => {
        e.preventDefault()
        isDismissed = true
        div.style.display = "none"
        createReopenButton()
        localStorage.setItem("crt_dismissed", "1")
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
        timerDiv.innerHTML = "⚠️ " + window.CRT_DATA.expiredMessage
        timerDiv.classList.add("expired")
        clearInterval(timerInterval)

        // Remove coupon via AJAX
        jQuery.post(window.CRT_DATA.ajax_url, {
          action: "crt_remove_expired_coupon",
          nonce: window.CRT_DATA.nonce,
        })

        // Update page to reflect coupon removal
        jQuery("body").trigger("wc_update_cart")

        if (localStorage.getItem("crt_dismissed")) {
          localStorage.removeItem("crt_dismissed")
          const reopenBtn = document.querySelector(".crt-reopen-btn")
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
      if (remaining === 60 && window.CRT_DATA.enable_sound) {
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
    let reopenBtn = document.querySelector(".crt-reopen-btn")

    if (reopenBtn) {
      reopenBtn.classList.add("show")
      return
    }

    reopenBtn = document.createElement("button")
    reopenBtn.className = "crt-reopen-btn show"
    reopenBtn.innerHTML = "⏳"
    reopenBtn.type = "button"

    reopenBtn.addEventListener("click", function (e) {
      e.preventDefault()
      isDismissed = false
      const timer = document.querySelector(".crt-timer")
      if (timer) {
        timer.style.display = "block"
        this.classList.remove("show")
        setTimeout(() => {
          reopenBtn.remove()
        }, 400)
      }
      localStorage.removeItem("crt_dismissed")
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
    if (window.CRT_DATA) {
      remaining = window.CRT_DATA.remaining
      totalDuration = window.CRT_DATA.duration

      if (localStorage.getItem("crt_dismissed")) {
        isDismissed = true
      }

      mountTimer()
    }
  })

  /**
   * Remount timer on cart update
   */
  jQuery(document.body).on("updated_cart_totals wc_fragments_loaded added_to_cart removed_from_cart", () => {
    if (!isDismissed && window.CRT_DATA) {
      remaining = window.CRT_DATA.remaining
      totalDuration = window.CRT_DATA.duration

      mounted = false
      clearInterval(timerInterval)
      timerExpired = false
      mountTimer()
    }
  })
})(window.jQuery || window.jQuery.noConflict())
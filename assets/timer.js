/**
 * Cart Reminder Timer for WooCommerce - Frontend Countdown
 */

;((jQuery) => {
  let mounted = false
  let remaining = 0
  let timerInterval = null
  let isDismissed = false
  let totalDuration = 0

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

    if (container.querySelector(".CRT-timer")) {
      return
    }

    const timerElement = createTimerElement()
    if (window.CRT_DATA.position === "top") {
      container.prepend(timerElement)
    } else {
      container.appendChild(timerElement)
    }

    const progressBar = timerElement.querySelector(".CRT-progress-bar")
    const timeSpan = timerElement.querySelector(".CRT-time")

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
    div.className = "CRT-timer " + window.CRT_DATA.color_scheme

    const html = `
			${window.CRT_DATA.show_progress ? '<div class="CRT-progress-container"><div class="CRT-progress-bar"></div></div>' : ""}
			<div class="CRT-content">
				<span class="CRT-message">⏳ ${window.CRT_DATA.messages[window.CRT_DATA.variant][window.CRT_DATA.loggedIn ? "user" : "guest"]}</span>
				<strong class="CRT-timer-value"><span class="CRT-time">00:00</span></strong>
				${window.CRT_DATA.dismissable ? '<button type="button" class="CRT-dismiss-btn">✕ Dismiss</button>' : ""}
			</div>
		`

    div.innerHTML = html

    // Attach dismiss handler
    if (window.CRT_DATA.dismissable) {
      const dismissBtn = div.querySelector(".CRT-dismiss-btn")
      dismissBtn.addEventListener("click", (e) => {
        e.preventDefault()
        isDismissed = true
        div.style.display = "none"
        createReopenButton()
        localStorage.setItem("CRT_dismissed", "1")
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
        timerDiv.innerHTML = "⚠️ Cart timer expired. Your items have been released."
        timerDiv.classList.add("expired")
        clearInterval(timerInterval)

        if (localStorage.getItem("CRT_dismissed")) {
          localStorage.removeItem("CRT_dismissed")
          const reopenBtn = document.querySelector(".CRT-reopen-btn")
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
    let reopenBtn = document.querySelector(".CRT-reopen-btn")

    if (reopenBtn) {
      reopenBtn.classList.add("show")
      return
    }

    reopenBtn = document.createElement("button")
    reopenBtn.className = "CRT-reopen-btn show"
    reopenBtn.innerHTML = "⏳"
    reopenBtn.type = "button"

    reopenBtn.addEventListener("click", function (e) {
      e.preventDefault()
      isDismissed = false
      const timer = document.querySelector(".CRT-timer")
      if (timer) {
        timer.style.display = "block"
        this.classList.remove("show")
        setTimeout(() => {
          reopenBtn.remove()
        }, 400)
      }
      localStorage.removeItem("CRT_dismissed")
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

      if (localStorage.getItem("CRT_dismissed")) {
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
      mounted = false
      clearInterval(timerInterval)
      remaining = window.CRT_DATA.remaining
      mountTimer()
    }
  })
})(window.jQuery || window.jQuery.noConflict())

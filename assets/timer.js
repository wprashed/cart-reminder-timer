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

    if (!window.dealcareCrtData) {
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
    if (window.dealcareCrtData.position === "top") {
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
    if (window.dealcareCrtData.show_on === "cart" || window.dealcareCrtData.show_on === "both") {
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
    if (window.dealcareCrtData.show_on === "checkout" || window.dealcareCrtData.show_on === "both") {
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
    div.className = "crt-timer " + window.dealcareCrtData.color_scheme

    const discountText =
      window.dealcareCrtData.discountInfo.type === "percent"
        ? window.dealcareCrtData.discountInfo.amount + "% off"
        : window.dealcareCrtData.discountInfo.amount + " " + window.dealcareCrtData.currency_symbol + " off"

    const html = `
      ${window.dealcareCrtData.show_progress ? '<div class="crt-progress-container"><div class="crt-progress-bar"></div></div>' : ""}
      <div class="crt-content">
        <span class="crt-message">⏳ ${window.dealcareCrtData.messages[window.dealcareCrtData.variant][window.dealcareCrtData.loggedIn ? "user" : "guest"]}</span>
        <div class="crt-discount-badge">${discountText}</div>
        <strong class="crt-timer-value"><span class="crt-time">00:00</span></strong>
        ${window.dealcareCrtData.dismissable ? '<button type="button" class="crt-dismiss-btn">✕ Dismiss</button>' : ""}
      </div>
    `

    div.innerHTML = html

    // Attach dismiss handler
    if (window.dealcareCrtData.dismissable) {
      const dismissBtn = div.querySelector(".crt-dismiss-btn")
      dismissBtn.addEventListener("click", (e) => {
        e.preventDefault()
        isDismissed = true
        div.style.display = "none"
        createReopenButton()
        localStorage.setItem("dealcare_crt_dismissed", "1")
      })
    }

    return div
  }

  /**
   * Updated AJAX action from the legacy coupon flow to the prefixed timer-expire action.
   * Start countdown timer
   */
  function startCountdown(targetSpan, timerDiv, progressBar) {
    function tick() {
      if (remaining <= 0) {
        timerExpired = true
        timerDiv.innerHTML = `<div class="crt-expired-notice">⚠️ <strong>${window.dealcareCrtData.expiredMessage}</strong><br><span class="crt-expired-notice-subtitle">Your special discount has expired. Add items again to get a new discount.</span></div>`
        timerDiv.classList.add("expired")
        clearInterval(timerInterval)

        jQuery.post(window.dealcareCrtData.ajax_url, {
          action: "dealcare_crt_expire_discount",
          nonce: window.dealcareCrtData.nonce,
        })

        // Update page to reflect discount removal
        jQuery("body").trigger("wc_update_cart")

        if (localStorage.getItem("dealcare_crt_dismissed")) {
          localStorage.removeItem("dealcare_crt_dismissed")
          const reopenBtn = document.querySelector(".crt-reopen-btn")
          if (reopenBtn) {
            reopenBtn.style.display = "none"
          }
        }
        return
      }

      targetSpan.textContent = formatTime(Math.floor(remaining))

      if (progressBar) {
        const progress = (remaining / totalDuration) * 100
        progressBar.style.width = progress + "%"
      }

      if (remaining === 60 && window.dealcareCrtData.enable_sound) {
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
      localStorage.removeItem("dealcare_crt_dismissed")
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
    if (window.dealcareCrtData) {
      remaining = window.dealcareCrtData.remaining
      totalDuration = window.dealcareCrtData.duration

      if (localStorage.getItem("dealcare_crt_dismissed")) {
        isDismissed = true
      }

      mountTimer()
    }
  })

  /**
   * Remount timer on cart update
   */
  jQuery(document.body).on("updated_cart_totals wc_fragments_loaded added_to_cart removed_from_cart", () => {
    if (!isDismissed && window.dealcareCrtData) {
      remaining = window.dealcareCrtData.remaining
      totalDuration = window.dealcareCrtData.duration

      mounted = false
      clearInterval(timerInterval)
      timerExpired = false
      mountTimer()
    }
  })
})(window.jQuery || window.jQuery.noConflict())

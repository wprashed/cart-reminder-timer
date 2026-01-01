;(($) => {
  let WCRT_DATA // Declare WCRT_DATA variable
  let mounted = false
  let remaining
  const totalDuration = WCRT_DATA.duration
  let timerInterval = null
  let isDismissed = false

  function format(sec) {
    const m = Math.floor(sec / 60)
    const s = sec % 60
    return m + ":" + (s < 10 ? "0" : "") + s
  }

  function mount() {
    if (mounted) return

    let container = null
    if (WCRT_DATA.show_on === "cart" || WCRT_DATA.show_on === "both") {
      container = document.querySelector(".woocommerce-cart-form") || document.querySelector(".wc-block-cart")
    }
    if (WCRT_DATA.show_on === "checkout" || WCRT_DATA.show_on === "both") {
      container = container || document.querySelector(".wc-block-checkout")
    }
    if (!container) return
    if (container.querySelector(".wcrt-timer")) return

    const div = document.createElement("div")
    div.className = "wcrt-timer " + WCRT_DATA.color_scheme
    div.innerHTML = `
            ${WCRT_DATA.show_progress ? '<div class="wcrt-progress-container"><div class="wcrt-progress-bar"></div></div>' : ""}
            <div class="wcrt-content">
                <span class="wcrt-message">⏳ ${WCRT_DATA.messages[WCRT_DATA.variant][WCRT_DATA.loggedIn ? "user" : "guest"]}</span>
                <strong class="wcrt-timer-value"><span class="wcrt-time">00:00</span></strong>
                ${WCRT_DATA.dismissable ? '<button type="button" class="wcrt-dismiss-btn">✕ Dismiss</button>' : ""}
            </div>
        `

    if (WCRT_DATA.position === "top") {
      container.prepend(div)
    } else {
      container.appendChild(div)
    }

    if (WCRT_DATA.dismissable) {
      const dismissBtn = div.querySelector(".wcrt-dismiss-btn")
      dismissBtn.addEventListener("click", () => {
        isDismissed = true
        div.style.display = "none"
        createReopenButton()
        localStorage.setItem("wcrt_dismissed", "1")
      })
    }

    const progressBar = div.querySelector(".wcrt-progress-bar")
    const timeSpan = div.querySelector(".wcrt-time")

    countdown(timeSpan, div, progressBar)
    mounted = true
  }

  function countdown(target, timerDiv, progressBar) {
    function tick() {
      if (remaining <= 0) {
        timerDiv.innerHTML = "⚠️ Cart timer expired. Your items have been released."
        timerDiv.classList.add("expired")
        clearInterval(timerInterval)

        if (localStorage.getItem("wcrt_dismissed")) {
          localStorage.removeItem("wcrt_dismissed")
          const reopenBtn = document.querySelector(".wcrt-reopen-btn")
          if (reopenBtn) reopenBtn.style.display = "none"
        }
        return
      }

      target.textContent = format(remaining)

      if (progressBar) {
        const progress = (remaining / totalDuration) * 100
        progressBar.style.width = progress + "%"
      }

      if (remaining === 60 && WCRT_DATA.enable_sound) {
        playSound()
        timerDiv.classList.add("critical")
      }

      remaining--
      timerInterval = setTimeout(tick, 1000)
    }
    tick()
  }

  function createReopenButton() {
    let reopenBtn = document.querySelector(".wcrt-reopen-btn")
    if (!reopenBtn) {
      reopenBtn = document.createElement("button")
      reopenBtn.className = "wcrt-reopen-btn show"
      reopenBtn.innerHTML = "⏳"
      reopenBtn.type = "button"
      reopenBtn.addEventListener("click", function () {
        isDismissed = false
        const timer = document.querySelector(".wcrt-timer")
        if (timer) {
          timer.style.display = "block"
          this.classList.remove("show")
          setTimeout(() => this.remove(), 400)
        }
        localStorage.removeItem("wcrt_dismissed")
      })
      document.body.appendChild(reopenBtn)
    }
  }

  function playSound() {
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
  }

  $(document).ready(() => {
    WCRT_DATA = window.WCRT_DATA // Assign WCRT_DATA from window object
    if (localStorage.getItem("wcrt_dismissed")) {
      isDismissed = true
    }
    mount()
  })

  $(document.body).on("updated_cart_totals wc_fragments_loaded added_to_cart removed_from_cart", () => {
    if (!isDismissed) {
      mounted = false
      clearInterval(timerInterval)
      remaining = WCRT_DATA.remaining
      mount()
    }
  })
})(window.jQuery) // Use window.jQuery to ensure jQuery is declared

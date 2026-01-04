/**
 * Cart Reminder Timer - Admin Panel JS
 */

;(($) => {
  const crtAdminData = window.crtAdminData || {
    currencySymbol: "$",
  }

  /**
   * Initialize admin panel tabs
   */
  function initTabs() {
    const tabButtons = document.querySelectorAll(".crt-tab-nav-item")
    const tabPanes = document.querySelectorAll(".crt-tab-pane")

    tabButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const tabName = this.getAttribute("data-tab")

        // Remove active class from all buttons and panes
        tabButtons.forEach((btn) => {
          btn.classList.remove("active")
        })
        tabPanes.forEach((pane) => {
          pane.classList.remove("active")
        })

        // Add active class to clicked button and corresponding pane
        this.classList.add("active")
        document.getElementById("crt-tab-" + tabName).classList.add("active")
      })
    })
  }

  /**
   * Update coupon unit display based on type
   */
  function initCouponUnitToggle() {
    const couponTypeSelect = document.getElementById("crt_coupon_type")
    const couponUnit = document.getElementById("crt_coupon_unit")

    if (!couponTypeSelect || !couponUnit) {
      return
    }

    couponTypeSelect.addEventListener("change", function () {
      if ("percent" === this.value) {
        couponUnit.textContent = "%"
      } else {
        couponUnit.textContent = crtAdminData.currencySymbol || "$"
      }
    })
  }

  /**
   * Initialize document
   */
  $(() => {
    initTabs()
    initCouponUnitToggle()
  })
})(window.jQuery)
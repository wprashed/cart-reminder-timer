/**
 * WooCommerce Cart Reminder Timer - Admin Panel JS
 */

;(($) => {
  const wcrtAdminData = window.wcrtAdminData || {
    currencySymbol: "$",
  }

  /**
   * Initialize admin panel tabs
   */
  function initTabs() {
    const tabButtons = document.querySelectorAll(".wcrt-tab-nav-item")
    const tabPanes = document.querySelectorAll(".wcrt-tab-pane")

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
        document.getElementById("wcrt-tab-" + tabName).classList.add("active")
      })
    })
  }

  /**
   * Update coupon unit display based on type
   */
  function initCouponUnitToggle() {
    const couponTypeSelect = document.getElementById("wcrt_coupon_type")
    const couponUnit = document.getElementById("wcrt_coupon_unit")

    if (!couponTypeSelect || !couponUnit) {
      return
    }

    couponTypeSelect.addEventListener("change", function () {
      if ("percent" === this.value) {
        couponUnit.textContent = "%"
      } else {
        couponUnit.textContent = wcrtAdminData.currencySymbol || "$"
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
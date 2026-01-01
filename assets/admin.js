/**
 * Cart Reminder Timer for WooCommerce - Admin Panel JS
 */

;(($) => {
  const CRTAdminData = window.CRTAdminData || {
    currencySymbol: "$",
  }

  /**
   * Initialize admin panel tabs
   */
  function initTabs() {
    const tabButtons = document.querySelectorAll(".CRT-tab-nav-item")
    const tabPanes = document.querySelectorAll(".CRT-tab-pane")

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
        document.getElementById("CRT-tab-" + tabName).classList.add("active")
      })
    })
  }

  /**
   * Update coupon unit display based on type
   */
  function initCouponUnitToggle() {
    const couponTypeSelect = document.getElementById("CRT_coupon_type")
    const couponUnit = document.getElementById("CRT_coupon_unit")

    if (!couponTypeSelect || !couponUnit) {
      return
    }

    couponTypeSelect.addEventListener("change", function () {
      if ("percent" === this.value) {
        couponUnit.textContent = "%"
      } else {
        couponUnit.textContent = CRTAdminData.currencySymbol || "$"
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
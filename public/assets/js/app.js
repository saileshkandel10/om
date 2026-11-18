try {
    var dropdownMenus = document.querySelectorAll(".dropdown-menu.stop");
    dropdownMenus.forEach(function (e) {
        e.addEventListener("click", function (e) {
            e.stopPropagation();
        });
    });
} catch (e) {}

try {
    lucide.createIcons();
} catch (e) {}

try {
    var themeColorToggle = document.getElementById("light-dark-mode");
    if (themeColorToggle) {
        themeColorToggle.addEventListener("click", function () {
            "light" === document.documentElement.getAttribute("data-bs-theme")
                ? document.documentElement.setAttribute("data-bs-theme", "dark")
                : document.documentElement.setAttribute("data-bs-theme", "light");
        });
    }
} catch (e) {}

try {
    var collapsedToggle = document.querySelector(".mobile-menu-btn");
    const overlay = document.querySelector(".startbar-overlay");

    const changeSidebarSize = () => {
        if (window.innerWidth < 992) {
            document.body.setAttribute("data-sidebar-size", "collapsed");
        } else {
            document.body.setAttribute("data-sidebar-size", "default");
        }
    };

    if (collapsedToggle) {
        collapsedToggle.addEventListener("click", function () {
            "collapsed" == document.body.getAttribute("data-sidebar-size")
                ? document.body.setAttribute("data-sidebar-size", "default")
                : document.body.setAttribute("data-sidebar-size", "collapsed");
        });
    }

    if (overlay) {
        overlay.addEventListener("click", () => {
            document.body.setAttribute("data-sidebar-size", "collapsed");
        });
    }

    window.addEventListener("resize", () => {
        changeSidebarSize();
    });
    changeSidebarSize();
} catch (e) {}

try {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map((e) => new bootstrap.Tooltip(e));
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (e) {
        return new bootstrap.Popover(e);
    });
} catch (e) {}

function windowScroll() {
    var e = document.getElementById("topbar-custom");
    if (e) {
        50 <= document.body.scrollTop || 50 <= document.documentElement.scrollTop
            ? e.classList.add("nav-sticky")
            : e.classList.remove("nav-sticky");
    }
}
window.addEventListener("scroll", (e) => {
    e.preventDefault();
    windowScroll();
});

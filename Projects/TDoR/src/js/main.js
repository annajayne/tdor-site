// Content warning popup
var seen_cwpopup = $.cookie('seen_cwpopup');
// seen_cwpopup = undefined; // TODO force on
if (seen_cwpopup === undefined)
{
    // No cookie, so show the popup
    $(".cw_popup").show();
} else
{
    // Cookie set so hide the popup
    $(".cw_popup").hide();
}

$(".cw_continue").click(function ()
{
    // Click to close so hide the popup and set the cookie
    $(".cw_popup").hide();
    $.cookie('seen_cwpopup', '1', { expires: 30 });

    // Return false to not continue with the navigation
    return false;
})

// Fireup the plugins
$(document).ready(function ()
{

    // initialise  slideshow
    $('.flexslider').flexslider({
        animation: "slide",
        start: function (slider)
        {
            $('body').removeClass('loading');
        }
    });

});
/**
 * Handles toggling the navigation menu for small screens.
 */
(function ()
{
    var button = document.getElementById('topnav').getElementsByTagName('div')[0],
        menu = document.getElementById('topnav').getElementsByTagName('ul')[0];

    if (undefined === button)
        return false;

    // Hide button if menu is missing or empty.
    if (undefined === menu || !menu.childNodes.length)
    {
        button.style.display = 'none';
        return false;
    }

    button.onclick = function ()
    {
        if (-1 == menu.className.indexOf('srt-menu'))
            menu.className = 'srt-menu';

        if (-1 != button.className.indexOf('toggled-on'))
        {
            button.className = button.className.replace(' toggled-on', '');
            menu.className = menu.className.replace(' toggled-on', '');
        } else
        {
            button.className += ' toggled-on';
            menu.className += ' toggled-on';
        }
    };
})();


// --- Sorttable v2 Auto‑Persist + Auto‑Restore -----------------------------
//
// Automatically persists the sort state to localStorage and restores it on page reload.
// This ensures that if the user has sorted a table, it will be resorted the same way if
// the page is reloaded.

(function ()
{
    const STORAGE_KEY = "sorttable-state";

    function getSortState(table)
    {
        const th = table.querySelector("th.sorttable_sorted, th.sorttable_sorted_reverse");
        if (!th)
        {
            return null;
        }

        const columnIndex = [...th.parentNode.children].indexOf(th);
        const direction = th.classList.contains("sorttable_sorted_reverse") ? "desc" : "asc";

        return { columnIndex, direction };
    }

    function restoreSortState(table, state)
    {
        if (!state)
        {
            return;
        }

        const th = table.querySelectorAll("th")[state.columnIndex];
        if (!th)
        {
            return;
        }

        // First click sets initial sort
        th.click();

        // If direction doesn't match, click again
        const isAsc = th.classList.contains("sorttable_sorted");
        if (state.direction === "desc" && isAsc)
        {
            th.click();
        }
    }


    function attachPersistence(table)
    {
        table.addEventListener("click", () =>
        {
            // Delay slightly so Sorttable has applied its classes
            setTimeout(() =>
            {
                const state = getSortState(table);
                if (state)
                {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
                }
            }, 0);
        });
    }

    document.addEventListener("DOMContentLoaded", () =>
    {
        const table = document.querySelector("table.sortable");
        if (!table)
        {
            return;
        }

        // Restore previous state
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved)
        {
            restoreSortState(table, JSON.parse(saved));
        }

        // Start tracking future sorts
        attachPersistence(table);
    });
})();

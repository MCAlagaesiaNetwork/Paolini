<?php
// Database connection details
$host = '';
$username = '';
$password = '';
$database = '';

$word_rows = [];
$db_error = null;

// Connect and fetch words
if ($host && $username && $database) {
    try {
        $conn = @new mysqli($host, $username, $password, $database);
        if ($conn->connect_errno) {
            $db_error = "Could not connect to the dictionary database.";
        } else {
            $sql = "SELECT word, definition, source FROM langman_words";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $word_rows[] = $row;
                }
            }
            $conn->close();
        }
    } catch (Exception $e) {
        $db_error = "Could not connect to the dictionary database.";
    }
} else {
    $db_error = "Database settings are not configured.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="dictionary, ancient, language, alagaesia, eragon, eldest, brisingr, inheritance, inheritance cycle, mmorpg, dragon, paolini, christopher, mcalagaesia, minecraft, arcaena, mmo, rpg, game">
    <meta name="title" content="Ancient Language Dictionary">
    <meta name="description" content="A lexicon of ancient language words from the World of Eragon.">
    <meta name="author" content="MCAlagaesia">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://arcaena.com/dictionary/">
    <meta property="og:title" content="Ancient Language Dictionary">
    <meta property="og:description" content="A lexicon of ancient language words from the World of Eragon.">
    <meta property="og:image" content="https://arcaena.com/img/metadata.jpg">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://arcaena.com/dictionary/">
    <meta property="twitter:title" content="Ancient Language Dictionary">
    <meta property="twitter:description" content="A lexicon of ancient language words from the World of Eragon.">
    <meta property="twitter:image" content="https://arcaena.com/img/metadata.jpg">
    
    <link rel="icon" href="../favicon.ico" type="image/x-icon" />
    <title>Ancient Language Dictionary</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<nav>
    <a href="https://mcalagaesia.com">Back to MCAlagaësia</a>
    <a href="https://discord.gg/EJSaEYd83f">Play Langman</a>
    <a href="https://arcaena.com/discord">Join the Community</a>
</nav>
<div class="site-logo">
    <img src="img/logo-arc.png" alt="MCAlagaësia Logo">
</div>
<div class="words-container">
    <h1>Ancient Language Dictionary</h1>
    <p style="font-size:1.15em;margin-bottom:22px;color:#eee;text-shadow:0 2px 6px #0007;">
        A lexicon of ancient language words from the World of Eragon.
    </p>
    <div class="table-search-container">
        <input
            type="search"
            id="word-search-input"
            class="table-search-input"
            placeholder="Search">
        <span class="search-icon" aria-hidden="true">&#128269;</span>
    </div>

    <div style="overflow-x:auto;">
        <table id="words_table" class="table-sort">
            <thead>
                <tr>
                    <th id="th-word" onclick="toggleSort('word')" tabindex="0" aria-sort="none"
                        onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleSort('word');}">
                        <span class="header-flex">
                            Ancient Word <span class="sort-arrow" id="word_sortarrow">↓</span>
                        </span>
                    </th>
                    <th id="th-definition" onclick="toggleSort('definition')" tabindex="0" aria-sort="none"
                        onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleSort('definition');}">
                        <span class="header-flex">
                            English Translation <span class="sort-arrow" id="def_sortarrow">↓</span>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <!-- Rows rendered by JS -->
            </tbody>
        </table>
    </div>
</div>
<div id="below-fold">
        <div class="content-columns">
            <div>
                <h2>About</h2>
                <p>The MCAlagaësia Project seeks to re-create Eragon's world of Alagaësia from the Inheritance Cycle by Christopher Paolini in Minecraft.</p>
            </div>
            <div>
                <h2>Contact</h2>
                <p>
                    <img src="https://mcalagaesia.com/img/discord-icon.webp" alt="Discord Icon" width="25">
                    <a href="https://arcaena.com/discord">https://arcaena.com/discord</a>
                </p>
                <p>
                    <img src="https://mcalagaesia.com/img/mail-icon.webp" alt="Email Icon" width="25">
                    <a href="mailto:contact@mcalagaesia.com">contact@mcalagaesia.com</a>
                </p>
            </div>
            <div>
                <h2>Copyright &copy; MCAlagaësia <?= date('Y') ?></h2>
                <p class="legal">The Inheritance Cycle and the World of Eragon are the property of Christopher Paolini and affiliated publishers.</p>
                <p class="legal">MCAlagaësia is not an official Minecraft product. MCAlagaësia is not associated with Mojang or Microsoft.</p>
            </div>
        </div>
    </div> 
<script>
const wordRows = <?php echo json_encode($word_rows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
wordRows.push({word: 'invalid entry', definition: 'the name of the ancient language'});
let sortState = { word: false, definition: false };
let lastSort = 'word';
let filterValue = '';

function escapeHTML(text) {
    return ('' + text)
        .replace(/&/g, "&amp;").replace(/</g, "&lt;")
        .replace(/>/g, "&gt;").replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function accentFold(str) {
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
}

// Performs filtering (and sorting) before rendering
function getFilteredRows() {
    let rows = wordRows;
    if (filterValue) {
        const v = accentFold(filterValue.toLowerCase());
        rows = rows.filter(row =>
            accentFold(row.word.toLowerCase()).includes(v) ||
            accentFold(row.definition.toLowerCase()).includes(v)
        );
    }
    let sorted = rows.slice();
    sorted.sort((a, b) => {
        let col = lastSort;
        let res = a[col].localeCompare(b[col], 'en', {sensitivity:'base'});
        return sortState[col] ? res : -res;
    });
    return sorted;
}

function highlightMatch(text, query) {
    if (!query) return escapeHTML(text);
    const normText = accentFold(text);
    const normQuery = accentFold(query);
    const regex = new RegExp(normQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
    let result = '';
    let lastIndex = 0;
    let match;

    while ((match = regex.exec(normText)) !== null) {
        const start = match.index;
        const end = regex.lastIndex;
        result += escapeHTML(text.slice(lastIndex, start));
        result += '<mark>' + escapeHTML(text.slice(start, end)) + '</mark>';
        lastIndex = end;
    }
    result += escapeHTML(text.slice(lastIndex));
    return result;
}

function renderTable(data) {
    document.querySelectorAll('.def-source-tooltip').forEach(el => el.remove());

    const tbody = document.getElementById('words_table').getElementsByTagName('tbody')[0];
    tbody.innerHTML = '';
    if (!data.length) {
        <?php if ($db_error): ?>
        // If DB is down, show a message in the table body
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="2" style="color:#ffc; background:#711c2e99;">Dictionary currently unavailable. Please try again later.</td>';
        tbody.appendChild(row);
        <?php else: ?>
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="2">No words found.</td>';
        tbody.appendChild(row);
        <?php endif; ?>
        return;
    }
    data.forEach(row => {
        const tr = document.createElement('tr');
        const wordHtml = filterValue ? highlightMatch(row.word, filterValue) : escapeHTML(row.word);
        const defHtml = filterValue ? highlightMatch(row.definition, filterValue) : escapeHTML(row.definition);
        let sourceHtml = '';
        if (row.source && row.source.trim()) {
            let sources = row.source.split(',').map(s => s.trim()).filter(s => !!s);
            if (sources.length > 0) {
                sourceHtml = `<span class="def-sources">` + 
                    sources.map((src, idx) => `<sup class="def-source-number" tabindex="0" aria-label="Source: ${escapeHTML(src)}" data-tooltip="${escapeHTML(src)}">${idx+1}</sup>`).join(' ') + 
                    `</span>`;
            }
        }
        tr.innerHTML = `
            <td class="td-copiable" data-label="Ancient Word">
                <span class="copiable-text">${wordHtml}</span>
                <span class="copy-tooltip">Click to copy</span>
            </td>
            <td class="td-copiable" data-label="English Translation">
                <span class="copiable-text">${defHtml}</span>
                ${sourceHtml}
                <span class="copy-tooltip">Click to copy</span>
            </td>`;
        tbody.appendChild(tr);
    });

    initialiseCopyToClipboard();
}

function setAriaSort(column, direction) {
    document.getElementById('th-word').setAttribute('aria-sort', column === 'word' ? (direction ? "ascending" : "descending") : "none");
    document.getElementById('th-definition').setAttribute('aria-sort', column === 'definition' ? (direction ? "ascending" : "descending") : "none");
}

function toggleSort(column) {
    if (lastSort === column) {
        sortState[column] = !sortState[column];
    } else {
        sortState[column] = true;
        lastSort = column;
    }
    updateSortIndicators(column);
    renderTable(getFilteredRows());
}

function updateSortIndicators(activeCol) {
    document.getElementById('th-word').classList.toggle('active-sort', activeCol === 'word');
    document.getElementById('th-definition').classList.toggle('active-sort', activeCol === 'definition');
    document.getElementById('word_sortarrow').textContent = (activeCol === 'word') ? (sortState.word ? '↑' : '↓') : '↓';
    document.getElementById('def_sortarrow').textContent = (activeCol === 'definition') ? (sortState.definition ? '↑' : '↓') : '↓';
    setAriaSort(activeCol, sortState[activeCol]);
}

document.addEventListener('DOMContentLoaded', function () {
    // Initial sort and render
    toggleSort('word');

    // --- Search Bar Handler ---
    const searchInput = document.getElementById('word-search-input');
    searchInput.addEventListener('input', function () {
        filterValue = this.value;
        renderTable(getFilteredRows());
    });

    initialiseSourceRendering();
});

function initialiseCopyToClipboard() {
    const showDelay = 380;
    document.querySelectorAll('.td-copiable').forEach(td => {
        const tooltip = td.querySelector('.copy-tooltip');
        let showTimer = null;

        // --- Hover (show after delay) ---
        td.addEventListener('mouseover', function(e) {
            // Only show tooltip if not over a source number
            if (e.target.classList.contains('def-source-number')) {
                tooltip.style.opacity = "0";
                tooltip.style.visibility = "hidden";
                if (showTimer) { clearTimeout(showTimer); showTimer = null; }
            } else if (e.currentTarget === td) {
                // Entering the td, but not a source button
                if (showTimer) clearTimeout(showTimer);
                showTimer = setTimeout(() => {
                    tooltip.style.visibility = "visible";
                    tooltip.style.opacity = "1";
                    showTimer = null;
                }, showDelay);
            }
        });

        td.addEventListener('mouseout', function(e) {
            // If leaving a source, but still inside cell (relatedTarget is inside td): show tooltip after delay
            if (e.target.classList.contains('def-source-number')) {
                if (td.contains(e.relatedTarget) && e.relatedTarget !== td) {
                    if (showTimer) clearTimeout(showTimer);
                    showTimer = setTimeout(() => {
                        tooltip.style.visibility = "visible";
                        tooltip.style.opacity = "1";
                        showTimer = null;
                    }, showDelay);
                }
            }
            // If we're fully leaving the whole cell
            if (!td.contains(e.relatedTarget)) {
                if (showTimer) { clearTimeout(showTimer); showTimer = null; }
                tooltip.style.opacity = "0";
                tooltip.style.visibility = "hidden";
            }
        });

        // --- Click copy behaviour ---
        td.addEventListener('click', function (e) {
            if (e.target.closest('.def-source-number')) return;
            let copiableText = td.querySelector('.copiable-text');
            let valToCopy = copiableText.textContent.trim();
            navigator.clipboard.writeText(valToCopy).then(() => {
                tooltip.textContent = "Copied!";
                tooltip.style.visibility = "visible";
                tooltip.style.opacity = "1";
                td.classList.add('td-canonical-copied');
                setTimeout(() => {
                    tooltip.textContent = "Click to copy";
                    tooltip.style.opacity = "0";
                    tooltip.style.visibility = "hidden";
                    td.classList.remove('td-canonical-copied');
                }, 1100);
            });
        });

        // --- Keyboard accessibility: enter/space to copy ---
        td.setAttribute('tabindex', '0');
        td.setAttribute('role', 'button');
        td.setAttribute('aria-label', 'Click to copy');
        td.addEventListener('keydown', function (e) {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                td.click();
            }
        });
    });
}

function initialiseSourceRendering() {
    if (window._sourceTooltipInitialised) return;
    window._sourceTooltipInitialised = true;

    let currentTooltip = null;
    let currentSourceBtn = null;
    let hideTimer = null;
    let hoverNumber = false, hoverTooltip = false;

    function markdownToHtml(text) {
        return text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, function(_, label, url) {
            const safeUrl = url.replace(/"/g, '&quot;');
            return `<a href="${safeUrl}" target="_blank" rel="noopener noreferrer">${label}</a>`;
        });
    }

    // Helper to show tooltip for a specific source button
    function showTooltipFor(elem) {
        if (currentTooltip) hideCurrentTooltip();
    
        hoverNumber = true;
        hoverTooltip = false;
    
        let tooltip = document.createElement('div');
        tooltip.className = "def-source-tooltip";
        tooltip.innerHTML = markdownToHtml(elem.getAttribute('data-tooltip'));
        
        // Temporarily hide-offscreen to measure width without a flicker
        tooltip.style.visibility = 'hidden';
        tooltip.style.left = '-9999px';
        tooltip.style.top = '0px';
    
        document.body.appendChild(tooltip);
    
        let rect = elem.getBoundingClientRect();
        let tooltipWidth = tooltip.offsetWidth;
    
        // Amount of space from the right edge of the element to the edge of the viewport
        let spaceRight = window.innerWidth - (rect.right);
    
        // Space needed to fit tooltip plus desired margin (e.g. 12px for some wiggle room)
        let requiredSpace = tooltipWidth + 12;
    
        // Decide placement
        if (spaceRight < requiredSpace) {
            // Not enough space right: show BELOW and CENTRED
            tooltip.style.left = Math.max(8, window.scrollX + rect.left + rect.width/2 - tooltipWidth/2) + 'px';
            tooltip.style.top = (rect.bottom + window.scrollY + 8) + 'px';
        } else {
            // Plenty of space right: show to the right (classic behaviour)
            tooltip.style.left = (rect.right + window.scrollX + 6) + 'px';
            tooltip.style.top = (rect.top + window.scrollY - 2) + 'px';
        }
        tooltip.style.visibility = '';
    
        currentTooltip = tooltip;
        currentSourceBtn = elem;
    
        // Track hover for both number and tooltip
        elem.addEventListener('mouseenter', onNumEnter);
        elem.addEventListener('mouseleave', onNumLeave);
        tooltip.addEventListener('mouseenter', onTooltipEnter);
        tooltip.addEventListener('mouseleave', onTooltipLeave);
    }

    function onNumEnter() {
        hoverNumber = true;
        clearTimeout(hideTimer);
    }
    function onNumLeave() {
        hoverNumber = false;
        scheduleHideIfNotHovered();
    }
    function onTooltipEnter() {
        hoverTooltip = true;
        clearTimeout(hideTimer);
    }
    function onTooltipLeave() {
        hoverTooltip = false;
        scheduleHideIfNotHovered();
    }
    function scheduleHideIfNotHovered() {
        clearTimeout(hideTimer);
        hideTimer = setTimeout(() => {
            if (!hoverNumber && !hoverTooltip) hideCurrentTooltip();
        }, 180);
    }
    function hideCurrentTooltip() {
        if (currentTooltip) {
            currentTooltip.removeEventListener('mouseenter', onTooltipEnter);
            currentTooltip.removeEventListener('mouseleave', onTooltipLeave);
            if (currentTooltip.parentNode) currentTooltip.parentNode.removeChild(currentTooltip);
        }
        if (currentSourceBtn) {
            currentSourceBtn.removeEventListener('mouseenter', onNumEnter);
            currentSourceBtn.removeEventListener('mouseleave', onNumLeave);
        }
        currentTooltip = null;
        currentSourceBtn = null;
        hoverNumber = false;
        hoverTooltip = false;
        clearTimeout(hideTimer);
    }

    // Mouseover to open tooltip (desktop)
    document.addEventListener('mouseover', function (e) {
        if (e.target.classList.contains('def-source-number')) {
            showTooltipFor(e.target);
        }
    });

    // Keyboard (focus) opens; closes on blur/escape
    document.addEventListener('focusin', function(e) {
        if (e.target.classList.contains('def-source-number')) showTooltipFor(e.target);
    });
    document.addEventListener('focusout', function(e) {
        if (e.target.classList.contains('def-source-number')) scheduleHideIfNotHovered();
    });
    document.addEventListener('keydown', function(e){
        if (e.key === "Escape") hideCurrentTooltip();
    });

    // Tapping or clicking toggles tooltip for that number (mobile & desktop)
    document.addEventListener('click', function(e) {
        // If click was inside tooltip, allow (do not close)
        if (currentTooltip && currentTooltip.contains(e.target)) return;
    
        let btn = e.target.closest('.def-source-number');
        if (btn) {
            if (btn !== currentSourceBtn) {
                // Not same as open: open tooltip
                showTooltipFor(btn);
            } else {
                // Same number: second tap, so hide
                hideCurrentTooltip();
            }
            // Don't propagate to document (avoid closing right after open)
            e.stopPropagation();
            e.preventDefault();
        } else {
            // Click outside: close tooltip if open
            hideCurrentTooltip();
        }
    });

    document.addEventListener('touchend', function (e) {
        // If touch was inside tooltip, allow (do not close)
        if (currentTooltip && currentTooltip.contains(e.target)) return;
    
        let btn = e.target.closest('.def-source-number');
        if (btn) {
            if (btn !== currentSourceBtn) {
                showTooltipFor(btn);
            } else {
                hideCurrentTooltip();
            }
            e.stopPropagation();
            e.preventDefault();
        } else {
            hideCurrentTooltip();
        }
    }, {passive:false});
}

document.addEventListener('keydown', function(e) {
    if (
        (e.ctrlKey || e.metaKey) && 
        (e.key === 'f' || e.key === 'F') &&
        !e.shiftKey && !e.altKey && !e.isComposing
    ) {
        e.preventDefault();
        const input = document.getElementById('word-search-input');
        if (input) {
            input.focus();
            input.select();
        }
    }
});
</script>
<script type="text/javascript" src="js/main.js"></script>
</body>
</html>

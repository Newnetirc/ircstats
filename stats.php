<?php
/**
 * @package stats
 * @version 0.1
 */
/*
Plugin Name: IRC Stats
Plugin URI: https://newnet.net/
Description: Stats for newnet IRC Network.
Version: 0.1
Text Domain: stats
Author: ChatGPT
Author URI: https://chat.openai.com
License: LGPLv2.1
*/
function fetch_stats() {
    $url = "https://stats.newnet.net/stats.json";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return null; // or handle the error as needed
    }
    curl_close($ch);
    return json_decode($response);
}
function stats_shortcode() {
    $stats = fetch_stats();
    if (!$stats) {
        return '<p>Unable to fetch statistics at this time. Please try again later.</p>';
    }
    ob_start();
    ?>
        <p>There are <?=htmlspecialchars($stats->usercount)?> users across <?=htmlspecialchars($stats->channelcount)?> channels.</p>
        <p>If the channel is set with <a href="https://docs.inspircd.org/3/modes/#channel-modes">chanmode +s</a> it will be omitted from this list.</p>
        <p>The table is sortable by clicking on the column headers.</p>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Users</th>
                        <th>Topic</th>
                    </tr>
                </thead>

                <tbody data-link="row" class="rowlink">
                    <?php foreach($stats->channels as $channel): ?>
                        <tr>
                            <td><a href="<?=htmlspecialchars($channel->webchatlink)?>"><?=htmlspecialchars($channel->name)?></a></td>
                            <td><?=htmlspecialchars($channel->usercount)?></td>
                            <td style="word-wrap: break-word; white-space: pre-wrap; max-width:700px"><?=htmlspecialchars($channel->topic)?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <hr>
        <p>Also available as <a href="https://stats.newnet.net/stats.json">JSON</a>.</p>

        <script>
            // sort stats page
            const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

            const comparer = (idx, asc) => (a, b) => ((v1, v2) =>
                v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
                )(getCellValue(asc ? b : a, idx), getCellValue(asc ? a : b, idx));

            // do the work...
            document.querySelectorAll('th').forEach(function(th) {
              th.addEventListener('click', (() => {
                const table = th.closest('table').querySelector("tbody");
                Array.from(table.querySelectorAll('tr'))
                    .sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
                    .forEach(tr => table.appendChild(tr) );
              }));
              th.style.cursor = 'pointer';
            });
        </script>
    <?php
    return ob_get_clean();
}

add_shortcode('stats', 'stats_shortcode');

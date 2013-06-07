<?php
use Scabbia\Extensions\Helpers\Html;
use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Http\Http;

?>

<div id="pqp-container" class="pQp hideDetails" style="display: none;">
    <div id="pQp" class="console">
        <table id="pqp-metrics" cellspacing="0">
            <tr>
                <td class="green" onclick="changeTab('console');">
                    <var><?php echo ($model['logcounts']['log'] + $model['logcounts']['error']); ?></var>
                    <h4>Console</h4>
                </td>
                <td class="blue" onclick="changeTab('time');">
                    <var><?php echo $model['timeTotals']['total']; ?></var>
                    <h4>Load Time</h4>
                </td>
                <td class="purple" onclick="changeTab('queries');">
                    <var><?php echo $model['logcounts']['query']; ?> Queries</var>
                    <h4>Database</h4>
                </td>
                <td class="orange" onclick="changeTab('memory');">
                    <var><?php echo $model['memoryTotals']['used']; ?></var>
                    <h4>Memory Used</h4>
                </td>
                <td class="red" onclick="changeTab('files');">
                    <var><?php echo count($model['files']); ?> Files</var>
                    <h4>Included</h4>
                </td>
            </tr>
        </table>

        <div id="pqp-console" class="pqp-box">
            <?php if (($model['logcounts']['log'] + $model['logcounts']['error']) == 0) { ?>
                <h3>This panel has no log items.</h3>
            <?php } else { ?>
                <table class="side" cellspacing="0">
                    <tr>
                        <td class="alt1"><var><?php echo $model['logcounts']['log']; ?></var><h4>Logs</h4></td>
                        <td class="alt2"><var><?php echo $model['logcounts']['error']; ?></var> <h4>Errors</h4></td>
                    </tr>
                    <tr>
                        <td class="alt3"><var><?php echo $model['logcounts']['memory']; ?></var> <h4>Memory</h4></td>
                        <td class="alt4"><var><?php echo $model['logcounts']['time']; ?></var> <h4>Time</h4></td>
                    </tr>
                </table>
                <table class="main" cellspacing="0">
                <?php
                    $class = '';
                    foreach ($model['logs'] as $log) {
                        if ($class == '') {
                            $class = 'alt';
                        } else {
                            $class = '';
                        }

                        if ($log['type'] == 'log' || $log['type'] == 'error') {
                ?>
                    <tr class="log-<?php echo $log['type']; ?>">
                        <td class="type"><?php echo $log['type']; ?></td>
                        <td class="<?php echo $class; ?>">
                        <?php if ($log['type'] == 'log') { ?>
                            <div><?php print_r($log['message']); ?></div>
                        <?php } elseif ($log['type'] == 'memory') { ?>
                            <div>
                                <div class="measure"><?php echo String::sizeCalc($log['data']); ?></div> <em><?php echo $log['datatype']; ?></em>: <?php print_r($log['message']); ?>
                                <?php if (isset($log['object'])) { ?>
                                <div><?php print_r($log['object']); ?></div>
                                <?php } ?>
                            </div>
                        <?php } elseif ($log['type'] == 'time') { ?>
                            <div><div class="measure"><?php echo String::timeCalc($log['data']); ?></div> <em><?php print_r($log['message']); ?></em></div>
                        <?php } elseif ($log['type'] == 'error') { ?>
                            <div><?php echo $log['message']; ?><div class="measure"><?php echo $log['location']; ?></div></div>
                        <?php } elseif ($log['type'] == 'query') { ?>
                            <div>
                                <div class="measure"><?php echo String::timeCalc($log['consumedTime']); ?></div>
                                <div><?php echo $log['message']; ?></div>
                                <div><em><?php echo $log['query']; ?></em></div>
                                <?php print_r($log['parameters']); ?>
                            </div>
                        <?php } ?>
                        </td>
                    </tr>
                <?php
                        }
                    }
                ?>
                </table>
            <?php } ?>
        </div>

        <div id="pqp-time" class="pqp-box">
            <?php if ($model['logcounts']['time'] == 0) { ?>
                <h3>This panel has no log items.</h3>
            <?php } else { ?>
                <table class="side" cellspacing="0">
                    <tr><td><var><?php echo $model['timeTotals']['total']; ?></var><h4>Load Time</h4></td></tr>
                    <tr><td class="alt"><var><?php echo $model['timeTotals']['allowed']; ?></var> <h4>Max Execution Time</h4></td></tr>
                </table>
                <table class="main" cellspacing="0">
                <?php
                    $class = '';
                    foreach ($model['logs'] as $log) {
                        if ($class == '') {
                            $class = 'alt';
                        } else {
                            $class = '';
                        }

                        if ($log['type'] == 'time') {
                ?>
                    <tr class="log-<?php echo $log['type']; ?>">
                        <td class="<?php echo $class; ?>">
                            <div><div class="measure"><?php echo String::timeCalc($log['data']); ?></div> <em><?php print_r($log['message']); ?></em></div>
                        </td>
                    </tr>
                <?php
                        }
                    }
                ?>
                </table>
            <?php } ?>
        </div>

        <div id="pqp-queries" class="pqp-box">
            <?php if ($model['logcounts']['query'] == 0) { ?>
                <h3>This panel has no log items.</h3>
            <?php } else { ?>
                <table class="side" cellspacing="0">
                    <tr><td><var><?php echo $model['logcounts']['time']; ?></var><h4>Total Queries</h4></td></tr>
                    <tr><td class="alt"><var><?php echo String::timeCalc($model['queryTotals']['time']); ?></var> <h4>Total Time</h4></td></tr>
                </table>

                <table class="main" cellspacing="0">
                <?php
                    $class = '';
                    foreach ($model['logs'] as $log) {
                        if ($class == '') {
                            $class = 'alt';
                        } else {
                            $class = '';
                        }

                        if ($log['type'] == 'query') {
                ?>
                    <tr>
                        <td class="<?php echo $class; ?>">
                            <div>
                                <div class="measure"><?php echo String::timeCalc($log['consumedTime']); ?></div>
                                <div><?php echo $log['message']; ?></div>
                                <div><em><?php
                                        $tReplaces = array();
                                        foreach ($log['parameters'] as $tKey => $tVal) {
                                            $tReplaces[':' . $tKey] = String::squote($tVal, true);
                                        }
                                        echo strtr($log['query'], $tReplaces);
                                ?></em></div>
                                <?php print_r($log['parameters']); ?>
                            </div>
                            <?php if (isset($log['explain'])) { ?>
                                <?php foreach ($log['explain'] as $tRow) { ?>
                                <div class="explain">
                                    . <?php echo $tRow['QUERY PLAN']; ?>
                                </div>
                                <?php } ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php
                        }
                    }
                ?>
                </table>
            <?php } ?>
        </div>

        <div id="pqp-memory" class="pqp-box">
            <?php if ($model['logcounts']['memory'] == 0) { ?>
                <h3>This panel has no log items.</h3>
            <?php } else { ?>
                <table class="side" cellspacing="0">
                    <tr><td><var><?php echo $model['memoryTotals']['used']; ?></var><h4>Used Memory</h4></td></tr>
                    <tr><td class="alt"><var><?php echo $model['memoryTotals']['total']; ?></var> <h4>Total Available</h4></td></tr>
                </table>
                <table class="main" cellspacing="0">
                <?php
                    $class = '';
                    foreach ($model['logs'] as $log) {
                        if ($class == '') {
                            $class = 'alt';
                        } else {
                            $class = '';
                        }

                        if ($log['type'] == 'memory') {
                ?>
                    <tr class="log-<?php echo $log['type']; ?>">
                        <td class="<?php echo $class; ?>">
                            <div><div class="measure"><?php echo String::sizeCalc($log['data']); ?></div> <em><?php echo $log['datatype']; ?></em>: <?php print_r($log['message']); ?></div>
                            <?php if (isset($log['object'])) { ?>
                                <div><?php print_r($log['object']); ?></div>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                <?php } ?>
                </table>
            <?php } ?>
        </div>

        <div id="pqp-files" class="pqp-box">
            <?php if ($model['fileTotals']['count'] == 0) { ?>
                <h3>This panel has no log items.</h3>
            <?php } else { ?>
                <table class="side" cellspacing="0">
                <tr><td><var><?php echo $model['fileTotals']['count']; ?></var><h4>Total Files</h4></td></tr>
                <tr><td class="alt"><var><?php echo $model['fileTotals']['size'] ?></var> <h4>Total Size</h4></td></tr>
                <tr><td><var><?php echo $model['fileTotals']['largest'] ?></var> <h4>Largest</h4></td></tr>
            </table>
            <table class="main" cellspacing="0">
                <?php
                    $class = '';
                    foreach ($model['files'] as $file) {
                        if ($class == '') {
                            $class = 'alt';
                        } else {
                            $class = '';
                        }
                ?>

                <tr><td class="<?php echo $class; ?>"><strong><?php echo $file['size']; ?></strong> <?php echo $file['message']; ?></td></tr>
                <?php } ?>

                </table>
            <?php } ?>
        </div>
    </div>

    <table id="pqp-footer" cellspacing="0">
        <tr>
            <td class="credit">
                <a href="http://particletree.com/">
                    <strong>PHP</strong>
                    <strong class="green">Q</strong><strong class="blue">u</strong><strong class="purple">i</strong><strong class="orange">c</strong><strong class="red">k</strong>
                    Profiler
                </a>
            </td>
            <td class="actions">
                <a href="https://github.com/larukedi/Scabbia-Framework/">Scabbia Framework</a>
            </td>
        </tr>
    </table>
</div>
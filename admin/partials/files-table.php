<table class="wp-list-table widefat fixed striped pages">
    <thead>
    <tr>
        <th scope="col" id="title" class="manage-column column-title" style="width:50%">
            <?php echo __('Title', 'tasdid-gateway') ?>
        </th>
        <th scope="col" id="author" class="manage-column column-author" style="width:50%">
            <?php echo __('Actions', 'tasdid-gateway') ?>
        </th>
    </tr>
    </thead>

    <tbody id="the-list">
    <?php
    $dir = plugin_dir_path(__FILE__) . "/../../logs/";

    // Open a directory, and read its contents
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != ".." && $file !== "index.php") {
                    ?>
                    <tr class="hentry entry">
                        <td style="width:50%"><?php echo $file ?></td>
                        <td style="width:50%">
                            <a href="<?php echo get_site_url() ?>/wp-content/plugins/tasdid-gateway/logs/<?php echo $file ?>"
                               class="button" download>
                                <?php echo __('download', 'tasdid-gateway') ?>
                            </a>
                        </td>
                    </tr>

                    <?php
                }
            }
            closedir($dh);
        }
    }
    ?>
    </tbody>

    <tfoot>
    <tr>
        <th scope="col" id="title" class="manage-column column-title" style="width:50%">
            <?php echo __('Title', 'tasdid-gateway') ?>
        </th>
        <th scope="col" id="author" class="manage-column column-author" style="width:50%">
            <?php echo __('Actions', 'tasdid-gateway') ?>
        </th>
    </tr>
    </tfoot>

</table>
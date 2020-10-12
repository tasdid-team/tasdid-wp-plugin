<?php

class Tasdid_Gateway_Admin
{

    public function add_menu_page_to_admin()
    {
        add_menu_page(__('Tasdid Logs', 'tasdid-gateway'), __('Tasdid Logs', 'tasdid-gateway'), 'manage_options', 'tasdid-logs.php', array($this, 'tasdid_admin_page'));
    }

    public function tasdid_admin_page()
    {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo __('Tasdid Logs', 'tasdid-gateway') ?></h1>
            <hr class="wp-header-end">
            <?php include "partials/files-table.php" ?>
        </div>
        <?php
    }

}
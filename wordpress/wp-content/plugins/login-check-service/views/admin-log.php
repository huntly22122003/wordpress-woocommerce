<div class="wrap">
    <h1>Log của user: <?php echo esc_html($email); ?></h1>
    <p><a href="<?php echo admin_url('admin.php?page=simple-auth'); ?>">&larr; Quay lại danh sách</a></p>

    <?php if (empty($logs)) : ?>
        <div class="notice notice-info"><p>Không có log nào cho email này.</p></div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Email</th>
                    <th>Action</th>
                    <th>Thời gian</th>
                    <th>IP</th>
                    <th>User Agent</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log) : ?>
                    <tr>
                        <td><?php echo esc_html($log['id']); ?></td>
                        <td><?php echo esc_html($log['email']); ?></td>
                        <td><?php echo esc_html($log['action']); ?></td>
                        <td><?php echo esc_html($log['login_time']); ?></td>
                        <td><?php echo esc_html($log['ip_address']); ?></td>
                        <td><?php echo esc_html($log['user_agent']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
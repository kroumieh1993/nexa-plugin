<?php
// expects: $properties
?>
<section class="nexa-properties-section">
    <div class="nexa-properties-header">
        <div>
            <h2 class="nexa-section-title">Your Properties</h2>
            <p class="nexa-section-subtitle">Manage the properties published on your website.</p>
        </div>
        <button type="button" class="nexa-btn" id="nexa-add-property-btn" disabled>
            + Add New Property (coming soon)
        </button>
    </div>

    <div class="nexa-card">
        <div class="nexa-table-wrapper">
            <table class="nexa-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>City</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Created</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $properties ) ) : ?>
                        <tr>
                            <td colspan="6" class="nexa-table-empty">
                                No properties found yet. You’ll be able to create them directly from here soon.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $properties as $p ) : ?>
                            <?php
                            $created = '—';
                            if ( ! empty( $p['created_at'] ) ) {
                                $timestamp = strtotime( $p['created_at'] );
                                if ( $timestamp ) {
                                    $created = date_i18n( get_option( 'date_format' ), $timestamp );
                                }
                            }

                            $delete_url = wp_nonce_url(
                                add_query_arg(
                                    [
                                        'nexa_action' => 'delete_property',
                                        'property_id' => intval( $p['id'] ),
                                    ],
                                    get_permalink()
                                ),
                                'nexa_delete_property_' . intval( $p['id'] )
                            );
                            ?>
                            <tr>
                                <td class="nexa-table-title">
                                    <?php echo esc_html( $p['title'] ?? '' ); ?>
                                </td>
                                <td><?php echo esc_html( $p['city'] ?? '' ); ?></td>
                                <td><?php echo esc_html( ucfirst( $p['category'] ?? '' ) ); ?></td>
                                <td>
                                    <?php
                                    if ( isset( $p['price'] ) ) {
                                        echo esc_html( number_format_i18n( $p['price'] ) );
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html( $created ); ?></td>
                                <td>
                                    <a class="nexa-link-muted" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('Delete this property?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

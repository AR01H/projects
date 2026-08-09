<?php
class TT_Spotlights_Handler extends TT_Base_Handler {
    public static function handle_request() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        
        $action = $_POST['action'] ?? '';
        $id     = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        
        $data = $_POST;
        unset( $data['action'], $data['_wpnonce'], $data['_wp_http_referer'], $data['id'] );
        
        if ( $action === 'create' ) {
            TT_Spotlights_Model::insert( $data );
            self::redirect_success( 'Created successfully.' );
        } elseif ( $action === 'update' && $id > 0 ) {
            TT_Spotlights_Model::update( $id, $data );
            self::redirect_success( 'Updated successfully.' );
        } elseif ( $action === 'delete' && $id > 0 ) {
            TT_Spotlights_Model::delete( $id );
            self::redirect_success( 'Deleted successfully.' );
        }
        
        self::redirect_error( 'Invalid action.' );
    }
}

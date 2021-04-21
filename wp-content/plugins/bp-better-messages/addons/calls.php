<?php
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'BP_Better_Messages_Calls' ) ):

    class BP_Better_Messages_Calls
    {

        public $new_thread_upload = false;

        public $audio = false;

        public $video = false;

        public $revertIcons = false;

        public static function instance()
        {
            static $instance = null;

            if ( null === $instance ) {
                $instance = new BP_Better_Messages_Calls();
            }

            return $instance;
        }


        public function __construct()
        {
            $this->audio       = BP_Better_Messages()->settings['audioCalls'] === '1';
            $this->video       = BP_Better_Messages()->settings['videoCalls'] === '1';
            $this->revertIcons = BP_Better_Messages()->settings['callsRevertIcons'] === '1';

            add_action( 'bp_better_messages_thread_pre_header',     array( $this, 'call_button' ), 10, 3 );
            add_action( 'bp_better_messages_thread_after_scroller', array( $this, 'html_content' ), 10, 3 );

            add_action( 'wp_ajax_bp_better_messages_record_missed_call',  array( $this, 'record_missed_call' ) );
            add_action( 'wp_ajax_bp_better_messages_record_offline_call', array( $this, 'record_offline_call' ) );

            add_action( 'wp_ajax_bp_better_messages_register_started_call',  array( $this, 'register_started_call' ) );
            add_action( 'wp_ajax_bp_better_messages_register_call_usage',    array( $this, 'register_call_usage' ) );

            if( BP_Better_Messages()->settings['callsLimitFriends'] === '1' ){
                add_filter('bp_better_messages_can_audio_call', array( $this, 'restrict_non_friends_calls'), 10, 3 );
                add_filter('bp_better_messages_can_video_call', array( $this, 'restrict_non_friends_calls'), 10, 3 );
            }
        }

        public function restrict_non_friends_calls( $can_call, $user_id, $thread_id ){
            if( ! function_exists( 'friends_check_friendship' ) ) return $can_call;

            $participants = BP_Better_Messages()->functions->get_participants($thread_id);
            if(count($participants['users']) !== 2) return false;

            unset($participants['users'][$user_id]);
            reset($participants['users']);

            $friend_id = key($participants['users']);

            /**
             * Allow users reply to calls even if not friends
             */
            if( current_user_can('manage_options') || user_can( $friend_id, 'manage_options' ) ) {
                return $can_call;
            }

            return friends_check_friendship($user_id, $friend_id);
        }

        public function can_audio_call_in_thread( $thread_id, $user_id ){
            $can_send_message = apply_filters('bp_better_messages_can_send_message', BP_Messages_Thread::check_access( $thread_id ), $user_id, $thread_id );
            if( ! $can_send_message  ) return false;

            $can_audio_call = apply_filters('bp_better_messages_can_audio_call', $can_send_message, $user_id, $thread_id );

            return $can_audio_call;
        }

        public function can_video_call_in_thread( $thread_id, $user_id ){
            $can_send_message = apply_filters('bp_better_messages_can_send_message', BP_Messages_Thread::check_access( $thread_id ), $user_id, $thread_id );
            if( ! $can_send_message  ) return false;

            $can_video_call = apply_filters('bp_better_messages_can_video_call', $can_send_message, $user_id, $thread_id );

            return $can_video_call;
        }

        public function register_started_call()
        {
            global $call_data;

            $user_id = get_current_user_id();
            $thread_id = intval($_REQUEST['thread_id']);
            $type = sanitize_text_field($_REQUEST['type']);
            $duration   = 0;

            $mins       = floor($duration / 60 % 60);
            $secs       = floor($duration % 60);
            $seconds    = sprintf('%02d:%02d', $mins, $secs);

            $call_data = [
                'caller_id'    => $user_id,
                'thread_id'    => $thread_id,
                'type'         => $type,
                'call_started' => bp_core_current_time(),
                'mins'      => $mins,
                'secs'      => $secs,
                'duration'  => $seconds,
            ];

            $can_send_message = apply_filters('bp_better_messages_can_send_message', BP_Messages_Thread::check_access( $thread_id ), $user_id, $thread_id );
            if( ! $can_send_message  ) return false;

            if( $type === 'audio' ){
                $can_audio_call = $this->can_audio_call_in_thread( $thread_id, $user_id );

                if( ! $can_audio_call ) return false;
                $message = '<span class="bpbm-call bpbm-call-audio call-accepted">' . sprintf( __( 'Audio call accepted <span class="bpbm-call-duration">(%s)</span>', 'bp-better-messages' ), $seconds )  . '</span>';

                $args = array(
                    'sender_id'   => $user_id,
                    'thread_id'   => $thread_id,
                    'content'     => $message,
                    'date_sent'  => bp_core_current_time()
                );

                add_action( 'messages_message_sent', array( $this, 'record_call_data' ), 5 );
                messages_new_message( $args );
                remove_action( 'messages_message_sent', array( $this, 'record_call_data' ), 5 );
            }


            if( $type === 'video' ){
                $can_video_call = $this->can_video_call_in_thread( $thread_id, $user_id );

                if( ! $can_video_call ) return false;
                $message = '<span class="bpbm-call bpbm-call-video call-accepted">' . sprintf( __( 'Video call accepted <span class="bpbm-call-duration">(%s)</span>', 'bp-better-messages' ), $seconds ) . '</span>';

                $args = array(
                    'sender_id'   => $user_id,
                    'thread_id'   => $thread_id,
                    'content'     => $message,
                    'date_sent'   => bp_core_current_time()
                );

                add_action( 'messages_message_sent', array( $this, 'record_call_data' ), 5 );
                messages_new_message( $args );
                remove_action( 'messages_message_sent', array( $this, 'record_call_data' ),5 );
            }

            wp_send_json( $call_data['message_id'] );

        }

        public function register_call_usage(){
            global $call_data;

            $user_id    = get_current_user_id();
            $message_id = intval( $_REQUEST['call_message_id'] );
            $message    = new BP_Messages_Message( $message_id );

            $duration   = intval( $_REQUEST['duration'] );

            $mins       = floor($duration / 60 % 60);
            $secs       = floor($duration % 60);
            $seconds    = sprintf('%02d:%02d', $mins, $secs);

            $call_data = [
                'mins'      => $mins,
                'secs'      => $secs,
                'duration'  => $seconds,
            ];

            if( $user_id !== $message->sender_id ) return false;

            $type = bp_messages_get_meta( $message_id, 'type', true );
            if( $type === 'video' ){
                $message->message    = apply_filters( 'messages_message_content_before_save', '<span class="bpbm-call bpbm-call-video call-accepted">' . sprintf( __( 'Video call accepted <span class="bpbm-call-duration">(%s)</span>', 'bp-better-messages' ), $seconds ) . '</span>', $message_id);
            } else if( $type === 'audio' ){
                $message->message    = apply_filters( 'messages_message_content_before_save', '<span class="bpbm-call bpbm-call-audio call-accepted">' . sprintf( __( 'Audio call accepted <span class="bpbm-call-duration">(%s)</span>', 'bp-better-messages' ), $seconds ) . '</span>', $message_id);
            } else {
                exit;
            }

            foreach( $call_data as $key => $value ){
                bp_messages_update_meta( $message_id, $key, sanitize_text_field( $value ) );
            }

            $message->recipients = $message->get_recipients();

            global $wpdb;
            $bp_prefix = bp_core_get_table_prefix();

            $wpdb->update("{$bp_prefix}bp_messages_messages", [
                'message' => $message->message
            ], [
                'id' => $message_id
            ]);

            $message->count_unread = false;

            BP_Better_Messages_Premium()->on_message_sent( $message );

            exit;
        }

        public function record_call_data( &$message ){
            global $call_data;

            $message_id = $message->id;

            bp_messages_update_meta( $message_id, 'bpbm_call', true );
            bp_messages_update_meta( $message_id, 'bpbm_call_accepted', true );
            foreach( $call_data as $key => $value ){
                bp_messages_update_meta( $message_id, $key, sanitize_text_field( $value ) );
            }

            $call_data['message_id'] = $message_id;
            $message->count_unread = false;
        }

        public function record_offline_call(){
            global $call_data;

            $user_id   = get_current_user_id();
            $thread_id = intval( $_REQUEST['thread_id'] );
            $type      = sanitize_text_field( $_REQUEST['type'] );

            $call_data = [
                'caller_id' => $user_id,
                'thread_id' => $thread_id,
                'type'      => $type,
            ];

            $can_send_message = apply_filters('bp_better_messages_can_send_message', BP_Messages_Thread::check_access( $thread_id ), $user_id, $thread_id );
            if( ! $can_send_message  ) return false;

            if( $type === 'audio' ){
                $can_audio_call = $this->can_audio_call_in_thread($thread_id, $user_id);

                if( ! $can_audio_call ) return false;
                $message = '<span class="bpbm-call bpbm-call-audio missed missed-offline">' . __( 'I tried to make audio call, but you were offline', 'bp-better-messages' )  . '</span>';

                $args = array(
                    'sender_id'   => $user_id,
                    'thread_id'   => $thread_id,
                    'content'     => $message,
                    'date_sent'  => bp_core_current_time()
                );

                add_action( 'messages_message_sent', array( $this, 'record_missed_call_data' ) );
                messages_new_message( $args );
                remove_action( 'messages_message_sent', array( $this, 'record_missed_call_data' ) );
            }


            if( $type === 'video' ){
                $can_video_call = $this->can_video_call_in_thread($thread_id, $user_id);

                if( ! $can_video_call ) return false;
                $message = '<span class="bpbm-call bpbm-call-video missed missed-offline">' . __( 'I tried to make video call, but you were offline', 'bp-better-messages' ) . '</span>';

                $args = array(
                    'sender_id'   => $user_id,
                    'thread_id'   => $thread_id,
                    'content'     => $message,
                    'date_sent'   => bp_core_current_time()
                );

                add_action( 'messages_message_sent', array( $this, 'record_missed_call_data' ) );
                messages_new_message( $args );
                remove_action( 'messages_message_sent', array( $this, 'record_missed_call_data' ) );
            }

            exit;
        }

        public function record_missed_call(){
            global $call_data;

            $user_id   = get_current_user_id();
            $thread_id = intval( $_REQUEST['thread_id'] );
            $type      = sanitize_text_field( $_REQUEST['type'] );
            $duration  = intval( $_REQUEST['duration'] );

            $mins    = floor($duration / 60 % 60);
            $secs    = floor($duration % 60);
            $seconds = sprintf('%02d:%02d', $mins, $secs);

            $call_data = [
                'caller_id' => $user_id,
                'thread_id' => $thread_id,
                'type'      => $type,
                'mins'      => $mins,
                'secs'      => $secs,
                'duration'  => $seconds,
            ];

            if( $type === 'audio' ){
                $can_audio_call = $this->can_audio_call_in_thread( $thread_id, $user_id );

                if( ! $can_audio_call ) return false;
                $message = '<span class="bpbm-call bpbm-call-audio missed">' . sprintf( __( 'Missed audio call <span class="bpbm-call-duration">(%s)</span>', 'bp-better-messages' ), $seconds ) . '</span>';

                $args = array(
                    'sender_id'   => $user_id,
                    'thread_id'   => $thread_id,
                    'content'     => $message,
                    'date_sent'  => bp_core_current_time()
                );

                add_action( 'messages_message_sent', array( $this, 'record_missed_call_data' ) );
                messages_new_message( $args );
                remove_action( 'messages_message_sent', array( $this, 'record_missed_call_data' ) );
            }


            if( $type === 'video' ){
                $can_video_call = $this->can_video_call_in_thread( $thread_id, $user_id );

                if( ! $can_video_call ) return false;
                $message = '<span class="bpbm-call bpbm-call-video missed">' . sprintf( __( 'Missed video call <span class="bpbm-call-duration">(%s)</span>', 'bp-better-messages' ), $seconds ) . '</span>';

                $args = array(
                    'sender_id'   => $user_id,
                    'thread_id'   => $thread_id,
                    'content'     => $message,
                    'date_sent'   => bp_core_current_time()
                );

                add_action( 'messages_message_sent', array( $this, 'record_missed_call_data' ) );
                messages_new_message( $args );
                remove_action( 'messages_message_sent', array( $this, 'record_missed_call_data' ) );
            }

            exit;
        }

        public function record_missed_call_data( $message ){
            global $call_data;

            $message_id = $message->id;

            bp_messages_add_meta( $message_id, 'bpbm_call', true );
            bp_messages_add_meta( $message_id, 'bpbm_missed_call', true );
            foreach( $call_data as $key => $value ){
                bp_messages_add_meta( $message_id, $key, sanitize_text_field( $value ) );
            }
        }

        public function call_button($thread_id, $participants, $is_mini){
            if( $is_mini ) return false;
            if( ! bpbm_fs()->can_use_premium_code() ) return false;
            $can_send_message = apply_filters('bp_better_messages_can_send_message', BP_Messages_Thread::check_access( $thread_id ), get_current_user_id(), $thread_id );
            if( ! $can_send_message  ) return false;

            $can_video_call = $this->can_video_call_in_thread( $thread_id, get_current_user_id() );
            $can_audio_call = $this->can_audio_call_in_thread( $thread_id, get_current_user_id() );

            if( count( $participants['recipients'] ) === 1 ){
                if( $this->video && $can_video_call ){
                    echo '<a href="#" class="video-call bpbm-can-be-hidden" data-user-id="' . $participants[ "recipients" ][0] . '"  title="' . __("Video Call", "bp-better-messages") . '"><i class="fas fa-video"></i></a>';
                }

                if( $this->audio && $can_audio_call ){
                    echo '<a href="#" class="audio-call bpbm-can-be-hidden" data-user-id="' . $participants[ "recipients" ][0] . '"  title="' . __("Audio Call", "bp-better-messages") . '"><i class="fas fa-phone"></i></a>';
                }
            }
        }

        public function html_content( $thread_id, $participants, $is_mini ){
            if( $is_mini ) return false;
            if( ! apply_filters('bp_better_messages_can_send_message', BP_Messages_Thread::check_access( $thread_id ), get_current_user_id(), $thread_id ) ) return false;

            $disable_mic_icon = 'fas fa-microphone-slash';
            $enable_mic_icon  = 'fas fa-microphone';

            $disable_video_icon = 'fas fa-video-slash';
            $enable_video_icon  = 'fas fa-video';

            if( $this->revertIcons ){
                $disable_mic_icon = 'fas fa-microphone';
                $enable_mic_icon  = 'fas fa-microphone-slash';

                $disable_video_icon = 'fas fa-video';
                $enable_video_icon  = 'fas fa-video-slash';
            }

            if( $this->video ){
            ?><div class="bp-messages-video-container bp-messages-call-container" style="display: none;" data-thread-id="<?php echo $thread_id ?>" data-my-name="<?php echo BP_Better_Messages_Functions()->get_name( get_current_user_id() ) ?>" data-my-avatar='<?php echo BP_Better_Messages_Functions()->get_avatar( get_current_user_id(), 100, [ 'html' => false ] ); ?>'>
                <span class="bp-messages-main-video" style="display:none;"></span>
                <span class="bp-messages-small-video"></span>

                <div class="bp-messages-main-placeholder">
                    <div class="bp-messages-placeholder-video"></div>
                    <div class="bp-messages-call-animation">
                        <?php echo BP_Better_Messages_Functions()->get_avatar($participants[ 'recipients' ][0], 100); ?>
                    </div>
                    <div class="bp-messages-placeholder-message">
                        <span class="bp-messages-placeholder-incoming-text"><?php _e('Incoming Call', 'bp-better-messages'); ?></span>
                        <span class="bp-messages-placeholder-outgoing-text"><?php _e('Calling...', 'bp-better-messages'); ?></span>
                    </div>
                </div>
                <div class="bp-messages-call-controls" style="display: none">
                    <div class="bpbm-call-out">
                        <span class="bpbm-switch-camera-video" style="display:none" title="<?php _e('Switch Camera', 'bp-better-messages'); ?>"><i class="fas fa-sync-alt"></i></span>

                        <span class="bpbm-disable-video" title="<?php _e('Disable Video', 'bp-better-messages'); ?>"><i class="<?php echo $disable_video_icon; ?>"></i></span>
                        <span class="bpbm-enable-video" title="<?php _e('Enable Video', 'bp-better-messages'); ?>" style="display: none"><i class="<?php echo $enable_video_icon; ?>"></i></span>

                        <span class="bpbm-disable-mic" title="<?php _e('Disable Microphone', 'bp-better-messages'); ?>"><i class="<?php echo $disable_mic_icon; ?>"></i></span>
                        <span class="bpbm-enable-mic" title="<?php _e('Enable Microphone', 'bp-better-messages'); ?>" style="display: none"><i class="<?php echo $enable_mic_icon; ?>"></i></span>
                        <span class="bpbm-cancel" title="<?php _e('Cancel', 'bp-better-messages'); ?>"><i class="fas fa-phone"></i></span>
                    </div>
                    <div class="bpbm-call-in">
                        <span class="bpbm-answer" data-user-id="<?php echo $participants[ 'recipients' ][0]; ?>" title="<?php _e('Answer', 'bp-better-messages'); ?>"><i class="fas fa-phone"></i></span>

                        <span class="bpbm-switch-camera-video" style="display:none" title="<?php _e('Switch Camera', 'bp-better-messages'); ?>"><i class="fas fa-sync-alt"></i></span>
                        <span class="bpbm-disable-video" title="<?php _e('Disable Video', 'bp-better-messages'); ?>"><i class="<?php echo $disable_video_icon; ?>"></i></span>
                        <span class="bpbm-enable-video" title="<?php _e('Enable Video', 'bp-better-messages'); ?>" style="display: none"><i class="<?php echo $enable_video_icon; ?>"></i></span>

                        <span class="bpbm-disable-mic" title="<?php _e('Disable Microphone', 'bp-better-messages'); ?>"><i class="<?php echo $disable_mic_icon; ?>"></i></span>
                        <span class="bpbm-enable-mic" title="<?php _e('Enable Microphone', 'bp-better-messages'); ?>" style="display: none"><i class="<?php echo $enable_mic_icon; ?>"></i></span>

                        <span class="bpbm-reject" data-user-id="<?php echo $participants[ 'recipients' ][0]; ?>" title="<?php _e('Reject', 'bp-better-messages'); ?>"><i class="fas fa-phone"></i></span>
                    </div>
                    <div class="bpbm-call-in-progress">
                        <span class="bpbm-switch-camera-video" style="display:none" title="<?php _e('Switch Camera', 'bp-better-messages'); ?>"><i class="fas fa-sync-alt"></i></span>

                        <span class="bpbm-disable-video" title="<?php _e('Disable Video', 'bp-better-messages'); ?>"><i class="<?php echo $disable_video_icon; ?>"></i></span>
                        <span class="bpbm-enable-video" title="<?php _e('Enable Video', 'bp-better-messages'); ?>" style="display: none"><i class="<?php echo $enable_video_icon; ?>"></i></span>

                        <span class="bpbm-disable-mic" title="<?php _e('Disable Microphone', 'bp-better-messages'); ?>"><i class="<?php echo $disable_mic_icon; ?>"></i></span>
                        <span class="bpbm-enable-mic" title="<?php _e('Enable Microphone', 'bp-better-messages'); ?>" style="display: none"><i class="<?php echo $enable_mic_icon; ?>"></i></span>

                        <span class="bpbm-call-end" title="<?php _e('End call', 'bp-better-messages'); ?>"><i class="fas fa-phone"></i></span>
                    </div>
                </div>
            </div>
            <?php
            }

            if( $this->audio ){ ?>
            <div class="bp-messages-audio-container bp-messages-call-container" style="display: none" data-thread-id="<?php echo $thread_id ?>" data-my-name="<?php echo BP_Better_Messages_Functions()->get_name( get_current_user_id() ) ?>" data-my-avatar='<?php echo BP_Better_Messages_Functions()->get_avatar( get_current_user_id(), 100, [ 'html' => false ] ); ?>'>

                <div class="bp-messages-main-placeholder">
                    <div class="bp-messages-call-animation">
                        <?php echo BP_Better_Messages_Functions()->get_avatar($participants[ 'recipients' ][0], 100); ?>
                    </div>
                    <div class="bp-messages-placeholder-message">
                        <span class="bp-messages-timer"></span>
                        <span class="bp-messages-placeholder-incoming-text"><?php _e('Incoming Call', 'bp-better-messages'); ?></span>
                        <span class="bp-messages-placeholder-outgoing-text"><?php _e('Calling...', 'bp-better-messages'); ?></span>
                    </div>
                </div>

                <div class="bp-messages-call-controls" style="display: none">
                    <div class="bpbm-call-out">
                        <span class="bpbm-disable-mic" title="<?php _e('Disable Microphone', 'bp-better-messages'); ?>"><i class="<?php echo $disable_mic_icon; ?>"></i></span>
                        <span class="bpbm-enable-mic" title="<?php _e('Enable Microphone', 'bp-better-messages'); ?>" style="display: none"><i class="<?php echo $enable_mic_icon; ?>"></i></span>
                        <span class="bpbm-cancel" title="<?php _e('Cancel', 'bp-better-messages'); ?>"><i class="fas fa-phone"></i></span>
                    </div>
                    <div class="bpbm-call-in">
                        <span class="bpbm-answer" data-user-id="<?php echo $participants[ 'recipients' ][0]; ?>" title="<?php _e('Answer', 'bp-better-messages'); ?>"><i class="fas fa-phone"></i></span>

                        <span class="bpbm-disable-mic" title="<?php _e('Disable Microphone', 'bp-better-messages'); ?>"><i class="<?php echo $disable_mic_icon; ?>"></i></span>
                        <span class="bpbm-enable-mic" title="<?php _e('Enable Microphone', 'bp-better-messages'); ?>" style="display: none"><i class="<?php echo $enable_mic_icon; ?>"></i></span>

                        <span class="bpbm-reject" data-user-id="<?php echo $participants[ 'recipients' ][0]; ?>" title="<?php _e('Reject', 'bp-better-messages'); ?>"><i class="fas fa-phone"></i></span>
                    </div>
                    <div class="bpbm-call-in-progress">
                        <span class="bpbm-disable-mic" title="<?php _e('Disable Microphone', 'bp-better-messages'); ?>"><i class="<?php echo $disable_mic_icon; ?>"></i></span>
                        <span class="bpbm-enable-mic" title="<?php _e('Enable Microphone', 'bp-better-messages'); ?>" style="display: none"><i class="<?php echo $enable_mic_icon; ?>"></i></span>

                        <span class="bpbm-call-end" title="<?php _e('End call', 'bp-better-messages'); ?>"><i class="fas fa-phone"></i></span>
                    </div>
                </div>


                <div class="bp-messages-audio-element">
                </div>
            </div>
            <?php }
        }

    }

endif;


function BP_Better_Messages_Calls()
{
    return BP_Better_Messages_Calls::instance();
}

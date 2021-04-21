<?php
/**
 * Single file template function
 **/
 if( ! defined("ABSPATH") ) die("Not Allowed");
 
function wpfm_get_file_detail( $file ) {
    
    $html = '';
    $html .= '<div class="wpfm-wrapper">';
    $html .= '<div id="file_detail_box_'.$file->id.'">';
        $html .= '<div class="close-modal-btn close-file_detail_box_'.$file->id.'"> ';
            $html .= '<img class="close-btn" src="'.WPFM_URL.'/images/closebt.svg">';
        $html .= '</div>';
        
        $html .= '<div class="wpfm-modal-content">';
            $html .= '<div class="row">';
            $html .= '<div class="col-sm-3">';
                $html .= '<div class="thumbnail">'.$file->thumb_image.'</div>';
                
                $html   .= $file->download_button;
                
                
                if( $file->is_updateable && class_exists('WPFM_FileRevision') ) {
                    
                    $html .= $file->update_button;
                }
                
                 if( $file->is_deletable ) {
                    
                    $html .= $file->delete_button;
                }
                
                // if( $file->is_share_enable ) {
                //     $html   .= $file->share_button;
                // }
                
                $html .= '<div class="table-responsive">';
                    $html .= '<table class="table table-bordered table-striped">';
                        $html .= '<tbody>';
                            $html .= '<tr>';
                                $html .= '<td><b>'.__( 'File Name', 'wpfm').'</b></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>'.$file->title.'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td><b>'.__( "File Size", "wpfm" ).'</b></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>'.$file->size.'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td><b>'.__( "File ID", "wpfm" ).'</b></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>'.$file->id.'</td>';
                            $html .= '</tr>';
                            
                            if( wpfm_is_keep_log_file_name() && class_exists('WPFM_FileRevision') ) {
                                $html .= $file->exist_filenames;
                            }
                            
                            $html .= '<tr>';
                                $html .= '<td><b>'.__('Total Downloads', 'wpfm').'</b></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>'.$file->total_downloads.'</td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td><b>'.__( "Created Date", "wpfm" ).'</b></td>';
                            $html .= '</tr>';
                            $html .= '<tr>';
                                $html .= '<td>'.$file->created_on.'</td>';
                            $html .= '</tr>';
                        $html .= '</tbody>';
                    $html .= '</table>';
                $html .= '</div>';
                
                
               $html .= '<button class="btn-block close-file_detail_box_'.$file->id.' btn btn-primary pull-right" data-close_frizi="modal">'.__("Close", "wpfm").'</button>';
                
            $html .= '</div>';
            $html .= '<div class="col-sm-9">';
                if( wpfm_is_user_to_edit_file() ) {
                    
                    $html .= '<div class="row">';
                        $html .= '<div class="col-sm-9">';
                            $html .= '<h3 class="file-title">'. sprintf(__("%s", "wpfm"), $file->title) .'</h3>';
                            $html .= '<p>'. sprintf(__("%s", "wpfm"), $file->description) .'</p>';
                        $html .= '</div>';
                    
                        $html .= '<div class="col-sm-3">';
                            $html .= '<button title="'.__('Edit Title','wpfm').'" class="btn btn-primary btn-sm wpfm-wrap pull-right file-edit-btn"><span class="dashicons dashicons-edit"></span></button>';
                            $html .= '<span class="clearfix"></span>';
                        $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<div class="row">';
                        $html .= '<div class="col-sm-12">';
                            $html .= '<div class="panel panel-primary title_dec_adit_wrapper">';
                                $html .= '<div class="panel-heading">';
                                    $html .= '<h3 class="panel-title">'.__( "File Data", "wpfm" ).'</h3>';
                                $html .= '</div>';
                                $html .= '<div class="panel-body">';
                                    $html .= '<h4>Title</h4>';
                                    $html .= '<input class="form-control file-title" value="'.esc_attr($file->title).'" type="text" data-id="'.$file->id.'">';
                                    $html .= '<h4>'.__("Description", "wpfm").'</h4>';
                                    $html .= '<textarea class="form-control file-description">'.esc_textarea($file->description).'</textarea>';
                                   
                                    
                                    $html .= '<h4 style="margin-top:16px">'.__("Change File Directory", "wpfm").'</h4>';
                                    $html .= '<select class="change-dir-name form control" style ="width:100%">';
                                    // $all_dir = wpfm_get_all_dir_name();
                                    // var_dump(wpfm_get_all_dir_name());
                                    // foreach(wpfm_get_all_dir_name() as $fid => $fname){
                                        
                                    //     $html .= '<option value="'.$fid.'">'.$fname.'</option>';
                                    // }
                                    
                                    $html .='</select>';
                                    
                                    $html .= '<button class="btn btn-success file-title-dec-save-btn" data-dismiss="modal">'.__( "Save Changes", "wpfm" ).'</button><button class="btn btn-info file-title-dec-cancel-adit-btn">'.__( "Cancel", "wpfm").'</button>';
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';  // col-sm-12
                    $html .= '</div>';  // row

                } else {
                    $html .= '<div class="row">';
                        $html .= '<div class="col-sm-12">';
                            $html .= '<h4 class="file-title">'. __( $file->title, "wpfm") .'</h4>';
                            $html .= '<p>'. __( $file->description, "wpfm") .'</p>';
                        $html .= '</div>';
                    $html .= '</div>';
                }
                
                if ( !empty($file->file_meta_info) ) {
                    $html .= '<div class="row">';
                        $html .= '<div class="col-sm-12">';
                            $html .= '<div class="meta-inforation">';
                                $html .= '<div class="panel panel-primary meta-info">';
                                    $html .= '<div class="panel-heading">';
                                        $html .= '<h3 class="panel-title">'.__( "File Meta", "wpfm" ).'</h3>';
                                    $html .= '</div>';
                                    $html .= '<div class="panel-body">';
                                        $html .= '<div>';
                                            
                                            $html .= $file->file_meta_info;
                                        $html .= '</div>';
                                        
                                        if( wpfm_is_user_to_edit_file() ) {
                                            $html .= '<a title="'.__('Edit Meta','wpfm').'" class="edit-meta-btn pull-right btn btn-primary"><span class="dashicons dashicons-edit"></span></a>';
                                        }
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="panel panel-primary meta-edit-from">';
                                    $html .= '<h4 class="panel-heading modal-title">'.__('Edit Meta', 'wpfm').'</h4>';
                                        $html .= '<form class="form save-meta-frm" data-file_id="'.esc_attr($file->id).'">';
                                            $html .= $file->file_meta_html; 
                                        $html .= '<button class="btn btn-success save-file-meta-btn">'.__('Save Meta','wpfm').'</button>';
        		                        $html .= ' <a class="btn btn-info go-to-meta-info-btn">'.__("Cancel", "wpfm").'</a>';
                                        $html .= '</form>';
                                    $html .= '<span class="clearfix"></span>';
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<hr>';
                }
                
                // Check against admin option _send_file
                if( wpfm_is_user_allow_to_send_file() ) {
                    $req = 'required';
                    if(is_admin()){
                        $req = '';
                    }
                    $html .= '<div class="panel panel-primary">';
                        $html .= '<div class="panel-heading">';
                            $html .= '<h3 class="panel-title">'.__('Send File','wpfm').'</h3>';
                        $html .= '</div>';
                        $html .= '<div class="panel-body">';
                            $html .= '<form class="form-horizontal wpfm-send-file-in-email">';
                                $html .= '<input type="hidden" name="action" value="wpfm_send_file_in_email">';
                                $html .= '<input type="hidden" name="file_id" value="'.esc_attr($file->id).'">';
                                $html .= '<div class="form-group">';
                                    $html .= '<label class="col-sm-2 control-label" for="emailaddress">'.__('Email','wpfm').'</label>';
                                    $html .= '<div class="col-sm-10"><input '.$req.' type="email" class="form-control" name="emailaddress" id="emailaddress"></div>';
                                $html .= '</div>';
                                $html .= '<div class="form-group">';
                                    $html .= '<label class="col-sm-2 control-label" for="subject">'.__('Subject','wpfm').'</label>';
                                    $html .= '<div class="col-sm-10"><input '.$req.' type="text" class="form-control" name="subject" id="subject"></div>';
                                $html .= '</div>';
                                $html .= '<div class="form-group">';
                                    $html .= '<label class="col-sm-2 control-label" for="message">'.__('Message (optional)','wpfm').'</label>';
                                    $html .= '<div class="col-sm-10"><textarea id="message" name="message" class="form-control"></textarea></div>';
                                $html .= '</div>';
                                $html .= '<div class="form-group"></div>';
                                $html .= '<div class="col-sm-offset-2 col-sm-10"><button class="btn btn-primary ">'.__('Send','wpfm').'</button></div>';
                                $html .= '<span class="wpfm-sending-file" style="display:none">'.__('Sending file ...','wpfm').'</span>';
                            $html .= '</form>';
                        $html .= '</div>';
                    $html .= '</div>';
                
                }
            $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>'; // end col-12
        $html .= '</div>'; // row    
    $html .= '</div>';
    $html .= '</div>';

    return apply_filters('wpfm_file_detail_template', $html, $file);
}
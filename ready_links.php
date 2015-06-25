<?php
if (!class_exists("SEO ReadyLinks")) {
    class ReadyLinks{
        var $plugin_url = '' ;
        var $plugin_path = '' ;
        var $lib_path = '';
        var $db =  array();
        var $version = '0.2';
        var $setting_page = '';
        var $options = '';
        var $option_name = 'SEO ReadyLinks';
        
        function __construct(){
            global $wpdb;
            $this->plugin_path = dirname(__FILE__);
            $this->lib_path = $this->plugin_path.'/lib';
            $this->db = array();
            
            $this->plugin_url = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));
            //register_activation_hook(__FILE__, array(&$this, 'install'));
            //register_deactivation_hook(__FILE__, array(&$this, 'uninstall'));
            add_action('admin_menu',array(&$this,'add_menu_pages'));
            add_action('admin_notices',  array(&$this,'admin_notices'));
        
        }
        
        
        function add_menu_pages(){

            $settings_page = add_options_page('Ready Links', 'Ready Links', 8, basename(__FILE__), array(&$this, 'ready_links'));

            add_action( "admin_print_scripts-$settings_page", array(&$this, 'js_scripts') );

            add_action( "admin_print_styles-$settings_page", array(&$this, 'css_styles') );

        }

        
        function js_scripts(){
                wp_enqueue_script('ready_links_js', plugins_url('/static/ready_links.js',__FILE__),array('jquery','jquery-ui-tabs'));
        }
        
        function css_styles(){
                wp_enqueue_style('ready_links_style', plugins_url('/static/ready_links.css',__FILE__));
        }
        
        function admin_notices(){
        }
        
        function install(){
        
            $options = $this->get_option();
            
            
            $options = array(
                        'version'=>$this->version,
                    );
            $this->update_option($options);
                
            
            
        }
        
        function get_option($name=''){
            
            if(empty($this->options)){
            
                $options = get_option($this->option_name);
                
            }else{
            
                $options = $this->options;
            }
            if(!$options) return false;
            if($name)
                return $options[$name];
            return $options;
        }
        
        function update_option($ops){
        
            if(is_array($ops)){
            
                $options = $this->get_option();
                
                foreach($ops as $key => $value){
                    
                    $options[$key] = $value;
                    
                }
                update_option($this->option_name,$options);
                $this->options = $options;
            }			
            
        
        }
        
        function uninstall(){
            global $wpdb;
            foreach($this->db as $table)
                $wpdb->query(  "DROP TABLE {$table}" );
            delete_option($this->option_name);
        }
        
        
        function ready_links(){
            if(wp_verify_nonce( $_POST['ready_links_ready_links'], 'ready_links_ready_links' )){
                @set_time_limit(0);
                @ini_set('memory_limit','256M');
                $download_url = $this->process();
            }
            @include($this->plugin_path.'/ready_links_ready_links.php');
        }
        
        function process(){
            $links = '';
            $html = true;
            $keywords = '';
            $results = '';
            if($_POST['export_format'] == 'url')
                $html = false;
            elseif($_POST['export_format'] == 'html'){
                $html = true;
                if($_POST['keywords']){
                    $keywords = wp_strip_all_tags($_POST['keywords']);
                }else{
                    $this->error_log( 'No Keyword Found!' );
                    return false;
                }
            }
            
            $links = $this->get_links($html);
            
            if( $html )
                $results = $this->equip_links($keywords,$links);
            else{
                $results = $links;
            }
            
            $filename = date("Y-m-d-H-i-s",time()).'.txt';
            $filepath = $this->plugin_path.'/files/'. $filename;
            
            $r = $this->generate_file($results,$filepath);
            if(!$r){
                $this->error_log( "Failed to generate file. Please check 'files' folder permission!" );
                return false;
            }else
                $download_url = $this->plugin_url.'/files/'.$filename;
            return $download_url;
        
        }
        
        function get_links($html = true){
            $is_site = isset($_POST['site'])?true:false;
            $is_post = isset($_POST['post'])?true:false;
            $is_page = isset($_POST['page'])?true:false;
            $is_category = isset($_POST['category'])?true:false;
            $is_tag = isset($_POST['tag'])?true:false;
            
            $links = '';
            
            if( !$is_site && !$is_page )
                $is_post = true;
            
            if($is_site)
                $links .= $this->get_site($html);
            
            if($is_post)
                $links .= $this->get_posts($html);
                
            if($is_page)
                $links .= $this->get_pages($html);
            
            if($is_category)
                $links .= $this->get_categories($html);
            
            if($is_tag)
                $links .= $this->get_tags($html);
                
            if( !$links ){
                $this->error_log( "No matched links found!" );
                return false;
            }
            return $links;
        
        }
        
        function get_categories($html = true){
            $ids = $this->get_terms('category');
            $links = array();
            foreach($ids as $id){
                $link = get_category_link($id);
                if($html)
                    $links[] = '<a href="'.$link.'">{keyword}</a>';
                else
                    $links[] = $link;
            }
            return implode("\r\n",$links)."\r\n";
            
        }
        
        function get_tags($html = true){
            $ids = $this->get_terms('post_tag');
            $links = array();
            foreach($ids as $id){
                $link = get_tag_link($id);
                if($html)
                    $links[] = '<a href="'.$link.'">{keyword}</a>';
                else
                    $links[] = $link;
            }
            return implode("\r\n",$links)."\r\n";
        }
        
        function get_site($html = true){
            
            $siteurl = get_bloginfo('url');
            if($html)
                return '<a href="'.$siteurl.'">{keyword}</a>'."\r\n";
            return $siteurl."\r\n";
        }
        
        function get_pages($html = true){
        
            $sql = $this->generate_sql(true);
            return $this->_get_posts($sql,$html);
            
        }
        
        function get_posts($html = true){
            $sql = $this->generate_sql();
            return $this->_get_posts($sql,$html);
            
        }
        
        
        function _get_posts($sql,$html){
            global $wpdb;
            $results = '';
            $posts = $wpdb->get_results($sql);
            
            $siteurl = get_bloginfo('url');
            foreach($posts as $k=> $post){
                $post->filter = 'sample';
                $url = get_permalink($post);
                if(!$url)
                    $url = $siteurl.'/?p='.$post->ID;
                    
                if($html)
                    $results .= '<a href="'.$url.'">{keyword}</a>'."\r\n";
                else
                    $results .= $url."\r\n";
            }
            
            return $results;
        }
        
        
        function generate_sql($is_page=false){
            global $wpdb;
            $cat = $this->get_cat();
            $datecmp = $this->get_datecmp();
            if($is_page)
                return "SELECT {$wpdb->posts}.* FROM {$wpdb->posts} where {$wpdb->posts}.post_status='publish' and {$wpdb->posts}.post_type='page' {$datecmp}";					
            if($cat == -1){
                return "SELECT {$wpdb->posts}.* FROM {$wpdb->posts} where {$wpdb->posts}.post_status='publish' and {$wpdb->posts}.post_type='post' {$datecmp}";					
            }else{
                return "SELECT {$wpdb->posts}.* FROM {$wpdb->posts}  INNER JOIN wp_term_relationships ON ({$wpdb->posts}.ID = wp_term_relationships.object_id) WHERE wp_term_relationships.term_taxonomy_id = '{$cat}' AND {$wpdb->posts}.post_type = 'post' AND {$wpdb->posts}.post_status = 'publish' {$datecmp} GROUP BY {$wpdb->posts}.ID ORDER BY {$wpdb->posts}.post_date DESC";
            }
        }
        
        function error_log($msg){
            $this->error .= "<p style='color:red;'>{$msg}</p>";
        }
        
        function print_error(){
            echo $this->error;
        }
        
        function redirect_to_current_page(){
            
            $this->redirect_to_page(admin_url('admin.php?page='.$_REQUEST['page'].'&success'));
        }
        
        function redirect_to_page($redir){
            echo "<meta http-equiv='refresh' content='0;url={$redir}' />";
            exit;
        }
        
        function generate_file($filecontent,$filepath){
            $r = file_put_contents($filepath,$filecontent);
            return $r;
        
        }
        
        function equip_links($keywords,$links){
            $keywords = preg_split( '/(\r\n|\r|\n)/', $keywords );
            $results = '';
            
            foreach($keywords as $keyword){
                $keyword = trim($keyword);
                if($keyword)
                    $results .= str_replace('{keyword}',$keyword,$links);
            }
            
            return $results;
        }
        

        
        function get_cat(){
            return $_POST['cat'];
        }
        
        function get_datecmp(){
            global $wpdb;
            $startDate = $endDate = '';
            if($_POST['startDate'])
                $startDate = date("Y-m-d H:i:s",strtotime(intval($_POST['startDate'])));
            if($_POST['endDate'])
                $endDate = date("Y-m-d H:i:s",strtotime(intval($_POST['endDate'])));
            
            if( $startDate === $endDate)
                $endDate = '';
            
            $datecmp = '';
                
            if($startDate && !$endDate){
                $datecmp = " and {$wpdb->posts}.post_date >= '{$startDate}' ";
            }elseif( $startDate && $endDate){
                $datecmp = " and {$wpdb->posts}.post_date >= '{$startDate}' and {$wpdb->posts}.post_date <= '{$endDate}' ";
            }elseif( !$startDate && $endDate )
                $datecmp = " and {$wpdb->posts}.post_date <= '{$endDate}' ";
                
            return $datecmp;
        
        }
        
        function print_categories(){
            
            $categories=  get_categories('hide_empty=1');
            
            $results = '<select name="cat">';
            $results .= '<option value="-1" selected="selected">All Categories</option>';
            foreach ($categories as $cat) {

                $option = "<option value='". $cat->term_taxonomy_id . "'>";

                $option .= $cat->cat_name;

                $option .= '</option>';

                $results.=$option;
            }
            
            $results .= '</select>'; 
               
            echo $results;
        }
        
        function get_terms($tax ='category'){
            $args = array('fields' => 'ids', 'get' => 'all');
            return get_terms($tax,$args);
        }
        
    }
    
    
}

if(!isset($ReadyLinks)){
        $ReadyLinks = new ReadyLinks();
}
?>

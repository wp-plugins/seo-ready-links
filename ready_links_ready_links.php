<div class="wrap">
<?php screen_icon('options-general');?>
<?php if($download_url){ ?>
<div id="message" class="updated">
  <p>
    <b>Download url:<a href="<?php echo $download_url;?>"> Please Right Click and Save As</a></b>
  </p>
</div>
<?php } ?>

<h2>Ready Links</h2>
<div id="poststuff" class="metabox-holder">
                    <div class="has-sidebar">
                        <div id="post-body-content" class="has-sidebar-content">
                        <div style="clear: both;">
                                <div class="postbox" style="display: block; width: 93%;">
                                <h3 style="cursor:default;"><span>Instructions</span></h3>
                                <span style="float:right; padding:15px;"/>
                                <img src="https://www.ultranoodle.com/wp-content/uploads/2015/05/Complete-Website-Solutions1.png" />
                                </span>
                                    <div class="inside">
                                         <p>1, Enter your keywords.</p>
                                         <p>2, Select Date Range. If empty, export all of them.</p>
                                         <p>3, Select Category.</p>
                                         <p>4, Select Export Type. Post by default.</p>
                                         <p>5, Select Export Format. HTML Hyperlink by default.</p>
                                          <p>6, Click Export button.</p>
                                    </div>
                                </div>
                                                
                            </div>

                        
                        </div>
                    </div>
</div>
<form method="post" >
<?php wp_nonce_field( 'ready_links_ready_links', 'ready_links_ready_links' ); ?>
<h4>Enter Keywords (anchor text) - one per line</h4>
<h3>It will print a full list for every keyword entered</h3>
<p><textarea name="keywords" cols="50" rows="15"></textarea></p>
<h4>Date Range</h4>
<p>From <input size="12" type="text" name="startDate" class="from" class="hasDatepicker" /> to <input size="12" name="endDate" type="text" class="to" class="hasDatepicker" /></p>
<h4>Category:</h4>
<p><?php $this->print_categories();?></p>

<h4>Export Type:</h4>
<p><input type="checkbox" name="site"> Site <input type="checkbox" name="post" checked> Post <input type="checkbox" name="page"> Page <input type="checkbox" name="category"> Category <input type="checkbox" name="tag"> Tag</p>

<h4>Export Format:</h4>
<p><input type="radio" name="export_format" value="html" checked>HTML Hyperlink <input type="radio" name="export_format" value="url">Just URL</p>

<p><input type="submit" class="button-primary" value="Export Links" /></p>

</form>
<p>Error:</p>
<?php $this->print_error();?>
</div>
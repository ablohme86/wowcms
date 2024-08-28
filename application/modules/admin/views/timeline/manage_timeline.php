<style>
    .center_stuff {

    display: flex;
    justify-content: center; /* Sentrer horisontalt */

align-items: center;     /* Sentrer vertikalt */
}
</style>

<section class="uk-section uk-section-xsmall" data-uk-height-viewport="expand: true">
    <div class="uk-container">
        <div class="uk-grid uk-grid-small uk-margin-small" data-uk-grid>
            <div class="uk-width-expand uk-heading-line">
                <h3 class="uk-h3"><i class="fas fa-timeline"></i> Game Mailer</h3>
            </div>
            <div class="uk-width-auto">
                <a href="<?= base_url('admin/timeline/create'); ?>" class="uk-icon-button"><i class="fas fa-plus"></i></a>
            </div>
        </div>
        <div class="uk-card uk-card-default uk-card-body buffa">
          

<table  style="padding: 10px; margin: 10px">
<tr>
	<td>

		  <table>

			<tr>
				<td>Realm:</td>
				<td><select name="realm_sel" style="width: 200px">
			                 <?php foreach ($this->wowrealm->getRealms()->result() as $charsMultiRealm) : ?>
										<option value="<?= $charsMultiRealm->id ?>"><?= $this->wowrealm->getRealmName($charsMultiRealm->id) ?></option> 

							 <?php endforeach; ?>



				
				</select>
				</td>
			</tr>
			<tr>
								<td>Character:</td>
				<td>
					<input type="text" style="width: 200px" name="searchChar" placeholder="Type in character name" value="<?=             $realm = $this->input->get('charname'); ?>">

				</td>
		  
		  
		  </tr>

		

		  
		  <tr>

		  								<td>Subject:</td>
				<td>
					<input type="text" style="width: 170px" name="searchChar" placeholder="From the Gods!" value="">

				</td>
		  
		</tr>
		  </table>
	</td>
	</tr>
	<tr>
	<td>
<textarea style="width: 100%; height: 200px">



Best regards from Game Master "insert here"!</textarea>

		</td></tr>
    
    <tr>
        
        <td class="center_stuff">
            <p style="margin: 0; margin-right: 10px">Gold</p><input type="text" name="golds" style="width: 30px">
            <p style="margin: 0;margin-right: 10px;margin-left: 10px">Silver</p><input type="text" name="silvers" style="width: 30px">
            <p style="margin: 0;margin-left: 10px"">Copper</p><input type="text" name="coppers" style="width: 30px">
            
        </td>
    </tr>
    <tr>
        <td>
            <hr style="margin: 0px">
        </td>
        
    </tr>
    <tr>
        <td>
           <list
        </td>
        
    </tr>
    </table>

				  


        </div>
		
    </div>
</section>

<script>
    var csrfName = "<?= $this->security->get_csrf_token_name() ?>";
    var csrfHash = "<?= $this->security->get_csrf_hash() ?>";

    function DeleteDownload(e, value) {
        e.preventDefault();

        $.ajax({
            url: "<?= base_url($lang . '/admin/timeline/delete'); ?>",
            method: "POST",
            data: {value, [globalThis.csrfName]: globalThis.csrfHash},
            dataType: "text",
            beforeSend: function () {
                $.amaran({
                    'theme': 'awesome info',
                    'content': {
                        title: '<?= $this->lang->line('notification_title_info'); ?>',
                        message: '<?= $this->lang->line('notification_checking'); ?>',
                        info: '',
                        icon: 'fas fa-sign-in-alt'
                    },
                    'delay': 500,
                    'position': 'top right',
                    'inEffect': 'slideRight',
                    'outEffect': 'slideRight'
                });
            },
            success: function (response) {
                if (response == true) {
                    $.amaran({
                        'theme': 'awesome ok',
                        'content': {
                            title: '<?= $this->lang->line('notification_title_success'); ?>',
                            message: '<?= $this->lang->line('notification_timeline_deleted'); ?>',
                            info: '',
                            icon: 'fas fa-check-circle'
                        },
                        'position': 'top right',
                        'inEffect': 'slideRight',
                        'outEffect': 'slideRight'
                    });
                } else {
                    $.amaran({
                        'theme': 'awesome error',
                        'content': {
                            title: '<?= $this->lang->line('notification_title_error'); ?>',
                            message: '<?= $this->lang->line('notification_general_error'); ?>',
                            info: '',
                            icon: 'fas fa-times-circle'
                        },
                        'position': 'top right',
                        'inEffect': 'slideRight',
                        'outEffect': 'slideRight'
                    });
                }
                setTimeout(function () {
                    location.reload();
                }, 1000);
            }
        });
    }
</script>
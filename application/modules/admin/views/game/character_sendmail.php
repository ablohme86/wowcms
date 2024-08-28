<style>
    .center_stuff {

    display: flex;
    justify-content: center; /* Sentrer horisontalt */

align-items: center;     /* Sentrer vertikalt */
}

.mailtable {
            border: 2px solid black; /* Svart ramme rundt hele tabellen */
            border-collapse: collapse; /* Slå sammen grensene mellom cellene */
            width: 80%; /* Juster bredden på tabellen */
            
        }

		      select {
            width: 100%;
            height: 100%;
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
          

<table class="mailtable">
<tr>
<td>

<table   style="width: 100%">
<tr>
	<td>

		  <table>

			<tr>
				<td>Realm:</td>
				<td><select name="realm_sel" style="width: 100%; height: 100%">
			                 <?php foreach ($this->wowrealm->getRealms()->result() as $charsMultiRealm) : ?>
										<option value="<?= $charsMultiRealm->id ?>"><?= $this->wowrealm->getRealmName($charsMultiRealm->id) ?></option> 

							 <?php endforeach; ?>



				
				</select>
				</td>
			</tr>
			<tr>
								<td>Character:</td>
				<td>
					<input type="text" name="searchChar" placeholder="Type in character name" value="<?=             $realm = $this->input->get('charname'); ?>">

				</td>
		  
		  
		  </tr>

		

		  
		  <tr>

			<td>Subject:</td>
				<td>
					<input type="text" name="searchChar" placeholder="From the Gods!" value="">

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
            <p style="margin: 0;margin-right: 10px;margin-left: 10px">Silver</p><input type="text" name="golds" style="width: 30px">
            <p style="margin: 0;margin-left: 10px"">Copper</p><input type="text" name="golds" style="width: 30px">
            
        </td>
    </tr>
    <tr>
        <td>
            <hr style="margin: 0px">
        </td>
        
    </tr>
    </table>
</td>
	<td style="padding: 30px">
<select id="myListbox"  name="myListbox" multiple size="20">
    <option value="option1">Alternativ 1</option>
    <option value="option2">Alternativ 2</option>
    <option value="option3">Alternativ 3</option>
    <option value="option4">Alternativ 4</option>
    <option value="option5">Alternativ 5</option>
</select>
<table>
	<tr>
		<td><input type="text" value="" placeholder="Enter item name"></td>
		<td><input type="submit" name="addd_item" value="Add Item"></td>
	</tr>


</table>

	</td>

</tr></table>
				  


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
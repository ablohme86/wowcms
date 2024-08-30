<?php

$toCharacter = $this->input->get('character');
$atRealm = $this->input->get('realm');	
echo '<script>	var MAX_ALLOWED_ITEMS = 0;
 </script>';
?>

<style>
	


</style>


<?php

// Lag en JavaScript-variabel for fargene
$qualityColors = [
	0 => '#889D9D',
	1 => '#FFFFFF',
	2 => '#1EFF0C',
	3 => '#0070FF',
	4 => '#A335EE',
	5 => '#FF8000',
	6 => '#E6CC80'
];

$maxAllowedItemsExp = [
	0 => 1,
	1 => 6,
	2 => 6,
];

echo "<script>var maxAllowedItemsExp = " . json_encode($maxAllowedItemsExp) . ";var qualityColors = " . json_encode($qualityColors) . ";</script>";

$expansionPack = $this->wowrealm->expansion($atRealm);
if (!$expansionPack && $expansionPack != "0")
{
	$realmSelected = false;
}
else
{
	echo '<script>MAX_ALLOWED_ITEMS = '.$maxAllowedItemsExp[$expansionPack].'; </script>';
}
?>


<link rel="stylesheet" href="<?= base_url() . 'application/modules/admin/assets/css/mailman.css'; ?>"/>
<link rel="stylesheet" href="<?= base_url() . 'application/modules/database/assets/css/database.css'; ?>"/>
<section class="uk-section uk-section-xsmall" data-uk-height-viewport="expand: true">
	<div class="uk-container">
		<div class="uk-grid uk-grid-small uk-margin-small" data-uk-grid>
			<div class="uk-width-expand uk-heading-line">
				<h3 class="uk-h3"><i class="fa-solid fa-envelope"></i> Game Mailer</h3>
			</div>
		
		</div>
		<div class="uk-card uk-card-default uk-card-body">
						<?= form_open('', 'id="sendmailSend" onsubmit="OnMailSendEvent(event)"'); ?>

			<div style="margin-top: 50px;margin-left: 100px; margin-right: 100px;margin-bottom: 50px">
	
				<div class="uk-child-width-expand@s uk-text-center" uk-grid>
									
									<div>
								<div class="uk-margin">
									
									<select class="uk-select"  name="realm_sel" id="realm_field" aria-label="Select"  title="Please select realm">
										<option value="0" disabled selected>Select Realm</option>
						<?php foreach ($this->wowrealm->getRealms()->result() as $charsMultiRealm) : ?>
							 <?php if ($charsMultiRealm->id == $atRealm): ?>
									<option value="<?= $charsMultiRealm->id ?>" selected><?= $this->wowrealm->getRealmName($charsMultiRealm->id) ?></option> 
							<?php else: ?>
									<option value="<?= $charsMultiRealm->id ?>"><?= $this->wowrealm->getRealmName($charsMultiRealm->id) ?></option> 
						
									<?php endif; ?>
						 <?php endforeach; ?>
									</select>
								</div>
								
								
								<div class="uk-margin">
									
									<input class="uk-input" type="text" id="rec_field" minlength="2" name="recipient_field" placeholder="<?= $this->lang->line('game_mailer_recipient') ?>" value="<?= $toCharacter ?>">
								</div>
					
								<div class="uk-margin">
									<textarea class="uk-textarea" rows="10" id="msg_field" minlength="2" placeholder="<?= $this->lang->line('ingame_msg_placeholder_prefix'); ?> <?= $toCharacter ?>! <?= $this->lang->line('ingame_msg_placeholder_msg'); ?><?= $this->lang->line('ingame_msg_placeholder_suffix'); ?>" aria-label="Textarea"></textarea>
								</div>
							
									<div class="uk-flex">

									<div class="input-wrapper" style="margin-left:0">
										<img src="<?= base_url() ?>/application/modules/database/assets/images/tooltip/icons/money-gold.gif" alt="Icon" class="input-icon">
										<input style="width: 70px;" type="text" id="gold_field" value="0">
									</div>
				<div class="input-wrapper">
										<img src="<?= base_url() ?>/application/modules/database/assets/images/tooltip/icons/money-silver.gif" alt="Icon" class="input-icon">
										<input style="width: 70px" type="text" id="silver_field" value="0">
									</div>
									<div class="input-wrapper">
										<img src="<?= base_url() ?>/application/modules/database/assets/images/tooltip/icons/money-copper.gif" alt="Icon" class="input-icon">
										<input style="width: 70px" type="text" id="copper_field" value="0">
									</div>

									</div>
							
								
								<div class="uk-margin">

					<div class="uk-flex uk-flex-center@m uk-flex-right@l" style="margin-bottom: 15px">
					<button type="submit" id="button_sendnow" class="uk-button-primary uk-width-1-1 uk-button-large">
						<i class="fa-solid fa-envelope"></i> <?= $this->lang->line('ingame_msg_send_mail') ?>
					</button>					</div>
								</div>
				
									</div>
									
									
									
									
									<div>
										<div class="uk-margin">
										  <input type="text"  class="uk-input"  id="itemname" placeholder="Search for items to send here">
										
										</div>
										<div id="autocomplete-results" class="search_result uk-text-left"></div>
											<div id="addedItemsContainer" class="added-items-container">
												  <!-- Dynamically added items will go here -->
											  </div>
									</div>
								
			<?= form_close(); ?>

				</div>
				
			
		</div>
		</div>
	</div>
</section>

<div id="tooltip" class="tooltip yesilcms-tooltip"></div>
<script type="text/javascript" src="<?= base_url() . 'application/modules/database/assets/js/tooltip.js'; ?>"></script>
<script type="text/javascript" src="<?= base_url() . 'application/modules/database/assets/js/jquery.dataTables.min.js'; ?>"></script>
<script type="text/javascript" src="<?= base_url() . 'application/modules/database/assets/js/dataTables.uikit.min.js'; ?>"></script>
<script type="text/javascript" src="/jquery.lazy.min.js"></script>

<script>
	
	function check_total_items()
	{
		console.log("Checking total items... total_items_added is: " + total_items_added + " and max item space is: " + MAX_ALLOWED_ITEMS);
		if (total_items_added >= MAX_ALLOWED_ITEMS) 
		{
			
			console.log("Ouch! We reached the limit of what we can send of items!!!, we have " + total_items_added + " and only " + MAX_ALLOWED_ITEMS + " of space in inventory!");
			$('#itemname').prop('disabled', true); // Deaktiver input-feltet
			$('#itemname').attr('placeholder', "<?= $this->lang->line('ingame_msg_placeholder_itemlist_prefix'); ?> "+MAX_ALLOWED_ITEMS+" <?= $this->lang->line('ingame_msg_placeholder_itemlist_suffix'); ?> " + $('#realm_field option:selected').text()); // Oppdater placeholder
			$('#itemname').val(''); // Clear input field only if limit is not reached
			if (total_items_added > MAX_ALLOWED_ITEMS)
			{
				console.log("OOOOMG! We have even MORE items than allowed! Disable ALL form of sending stuff from here! *Lock*");
				$('#button_sendnow').prop('disabled', true);
			}
	
		} 
		else 
		{
			console.log("We are in the total clear on the total-items-space-thingy! *Phew*");
			$('#button_sendnow').prop('disabled', false);
			$('#itemname').attr('placeholder', "<?= $this->lang->line('ingame_msg_placeholder_itemsearch'); ?>");
			$('#itemname').prop('disabled', false); // Deaktiver input-feltet
		}
	
	}
var total_items_added = 0;
// JavaScript and jQuery code
$(document)
	.ready(function ()
	{
		let selectedItem = null; // Variable to store selected item

		$('#itemname')
			.on('input', function ()
			{
				let query = $(this)
					.val();
				let realm = $('#realm_sel')
					.val(); // Assumed dropdown for realm

				if(query.length > 3)
				{ // Check if user has typed more than 3 characters
					let apiUrl;

					if(isInt(query))
					{
						apiUrl = `<?= base_url() ?>/api/v1/item/${query}/${realm}`;
					}
					else
					{
						apiUrl = `<?= base_url() ?>/en/api/v1/search_itemsonly?q=${query}&realm=${realm}`;
					}

					$.ajax(
					{
						url: apiUrl,
						type: 'GET',
						dataType: 'json',
						success: function (data)
						{
							check_total_items();
							$('#autocomplete-results')
								.empty()
								.show();

							if(data.result && data.result.length > 0)
							{
								data.result.forEach(function (item)
								{
									let color = qualityColors[item.Quality] || 'Green';

									$('#autocomplete-results')
										.append(`
									<div class="autocomplete-item" data-id="${item.entry}" data-name="${item.name}" data-color="${item.Quality}" data-icon="${item.icon}" style="color: ${color};">
										<img src="<?= base_url() ?>/application/modules/database/assets/images/icons/${item.icon}.png" alt="${item.name}">
										${item.name}
									</div>
								`);
								});

								$('.autocomplete-item')
									.on('click', function ()
									{
										let item = $(this)
											.data(); // Store item data
										addItemToContainer(item); // Call function to add item to container
										$('#itemname')
											.val(''); // Clear input field
										$('#autocomplete-results')
											.hide(); // Hide results
									});
							}
							else
							{
								$('#autocomplete-results')
									.append('<div class="autocomplete-item">No hits nor titts!</div>');
							}
						},
						error: function (jqXHR, textStatus, errorThrown)
						{
							console.error('Feil: ' + textStatus);
						}
					});
				}
				else
				{
					$('#autocomplete-results')
						.hide(); // Hide results if less than 4 characters
				}
			});


		$('#itemname')
			.on('click', function ()
			{

				$('#itemname')
					.val(''); // Clear input field only if limit is not reached

			});


		$('#realm_field')
			.on('change', function ()
			{
				var selectedValue = $(this)
					.val(); // Henter verdien av det valgte alternativet

				// http://wowragnaros.com:8888/en/api/v1/realm/1
				$.ajax(
				{
					url: '<?= base_url() ?>/en/api/v1/realm/' + selectedValue,
					type: 'GET',
					success: function (response)
					{
						if(response != 'false')
						{
							var resfy = JSON.parse(response);

							console.log("EXP is now: " + resfy.expansion);

							MAX_ALLOWED_ITEMS = maxAllowedItemsExp[resfy.expansion];
							check_total_items();
							console.log('Max allowed items on this realm is: ' + MAX_ALLOWED_ITEMS);
						}
					}
				});


			});

		$('#itemname')
			.on('blur', function ()
			{
				// Delay the hiding to allow click event to fire on autocomplete items
				setTimeout(() =>
				{
					let results = $('#autocomplete-results')
						.text()
						.trim();
					if(results === "No hits nor titts!")
					{
						$('#autocomplete-results')
							.hide();
					}
				}, 200);
			});

		$('#removeItem')
			.on('click', function ()
			{
				$('.added-item.selected')
					.remove(); // Remove selected items
			});


		function isInt(value)
		{
			return !isNaN(value) && parseInt(Number(value)) == value && !isNaN(parseInt(value, 10));
		}
	});

function addItemToContainer(item)
{
	let container = $('#addedItemsContainer');
	let existingItem = container.find(`.added-item[data-id="${item.id}"]`);

	if(existingItem.length === 0)
	{
		let color = qualityColors[item.color] || 'Green';
		container.append(`
				<div class="added-item" data-id="${item.id}" data-icon="${item.icon}" style="color: ${item.color};">
					<div style="display: flex; align-items: center;" class="uk-text-left">
						<img src="<?= base_url() ?>/application/modules/database/assets/images/icons/${item.icon}.png" alt="Icon" class="itemlist_img">
						<p style="color: ${color};">${item.name}</p>
					</div>
					<button class="button_remove" onclick="removeItem(this)"></button>
				</div>
			`);
		total_items_added++;
		check_total_items();
	}
}

function removeItem(button)
{
	$(button)
		.closest('.added-item')
		.remove();
	total_items_added--;
	check_total_items();
}

var csrfName = "<?= $this->security->get_csrf_token_name() ?>";
var csrfHash = "<?= $this->security->get_csrf_hash() ?>";

function OnMailSendEvent(e)
{

	e.preventDefault();

	var mf = $('#msg_field').val();
	var rf = $('#rec_field').val();
	var rid_f = $('#realm_field').val();
	var gf = $('#gold_field').val();
	var sf = $('#silver_field').val();
	var cf = $('#copper_field').val();
	var err = 0;
	console.log(rid_f + " and err is: " + err);
	if(mf.length < 1)
	{
		err = 1;
		flashRed($('#msg_field'), 350, 65); // 2 sekunder, med blink hvert 300ms

	}
	if(rf.length < 2)
	{
		err = 1;

		flashRed($('#rec_field'), 350, 65); // 2 sekunder, med blink hvert 300ms
	}
	if(rid_f == null)
	{
		err = 1;
		console.log("No rid!");
		flashRed($('#realm_field'), 350, 65); // 2 sekunder, med blink hvert 300ms

	}
	if (err == 1)
	{
		console.log("Err detected, returning..");
		return;
	}

	// Hente alle item ID's fra addedItemsContainer
	var items = [];
	$('#addedItemsContainer .added-item')
		.each(function ()
		{
			items.push($(this)
				.data('id'));
		});

	// Sending data via AJAX
	$.ajax(
	{
		url: "<?= base_url($lang . '/api/v1/mailman/send'); ?>",
		method: "POST",
		data:
		{
			[globalThis.csrfName]: globalThis.csrfHash,
			message: mf,
			recipient: rf,
			realm_id: rid_f,
			gold: gf,
			silver: sf,
			copper: cf,
			items: items // Sender listen med item ID's
		},
		dataType: "text",
		beforeSend: function ()
		{
			console.log("Sending mail...");
			$.amaran(
			{
				'theme': 'awesome info',
				'content':
				{
					title: '<?= $this->lang->line('
					notification_title_info '); ?>',
					message: '<?= $this->lang->line('
					notification_ingamemail_sending '); ?>' + rf,
					info: '',
					icon: 'fas fa-sign-in-alt'
				},
				'delay': 1000,
				'position': 'top right',
				'inEffect': 'slideRight',
				'outEffect': 'slideRight'
			});

		},
		success: function (response)
		{
	
			console.log("RESPONZEE: " + response);
			if(response)
			{
			

				$.amaran(
				{
					'theme': 'awesome ok',
					'content':
					{
						title: '<?= $this->lang->line('
						notification_title_great_success '); ?>',
						message: '<?= $this->lang->line('
						notification_ingame_mail_sent '); ?>',
						info: '',
						icon: 'fas fa-check-circle'
					},
					'delay': 5000,
					'position': 'top right',
					'inEffect': 'slideRight',
					'outEffect': 'slideRight'
				});
				console.log("Reply is: " + response);
			}

			setTimeout(function ()
			{
				//location.reload();
			}, 3500);
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log(errorThrown);
			console.log(textStatus);
			console.log(jqXHR);
			$.amaran(
			{
				'theme': 'awesome error',
				'content':
				{
					title: '<?= $this->lang->line('notification_title_sendmail_error'); ?>',
					message: errorThrown,
					info: "",
					icon: 'fas fa-check-circle'
				},
				'delay': 5000,
				'position': 'top right',
				'inEffect': 'slideRight',
				'outEffect': 'slideRight'
			});
		}
	});

	console.log("Will send mail to " + rf + " now...");
}

function send_error(msg, title)
{
	$.amaran(
	{
		'theme': 'awesome error',
		'content':
		{
			title: title,
			message: msg,
			info: '',
			icon: 'fas fa-solid fa-triangle-exclamation'
		},
		'delay': 3000,
		'position': 'top right',
		'inEffect': 'slideRight',
		'outEffect': 'slideRight'
	});
}

function flashRed(element, duration, interval)
{
	var end = Date.now() + duration; // Beregn sluttidspunkt
	var isRed = false;

	var intervalId = setInterval(function ()
	{
		if(Date.now() >= end)
		{
			clearInterval(intervalId); // Stopp blinking når tiden er ute
			element.css('background-color', ''); // Tilbakestill bakgrunnsfarge
		}
		else
		{
			// Bytt mellom rødt og standardfarge
			element.css('background-color', isRed ? '' : '#d10000');
			isRed = !isRed;
		}
	}, interval);
}
	
</script>


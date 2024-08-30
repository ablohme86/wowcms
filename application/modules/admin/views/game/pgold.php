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

echo "<script>
	var qualityColors = " . json_encode($qualityColors) . ";
</script>";

$toCharacter = $this->input->get('character');
$atRealm = $this->input->get('realm');


function send_command($cmd)
{
	$username = 'ablohme';
	$password = 'battery123';

	$host = "10.0.1.30";
	$soapport = 7878;
	$command = $cmd;

	$client = new SoapClient(NULL,
	array(
		"location" => "http://$host:$soapport/",
		"uri" => "urn:MaNGOS",
		"style" => SOAP_RPC,
		'login' => $username,
		'password' => $password
	));

	try 
	{
		$result = $client->executeCommand(new SoapParam($command, "command"));
		echo $result;
		return $result;
	}
	catch (Exception $e)
	{
		echo "Command failed! Reason:<br />\n";
		echo $e->getMessage() . " from: "  . $_SERVER["REMOTE_ADDR"];
		return false;
	}

}

//send_command("send items sneaky \"Congratulations!\" \"Here is your level 60 starting gear mate!\" 16713:1 16711:2");

?>



<link rel="stylesheet" href="<?= base_url() . 'application/modules/database/assets/css/database.css'; ?>"/>
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
			<div class="sidebyside_div">
		 <div class="main_mailbox_div">
			<table class="mail_data_info">
				<tr>
					<td><p class="mail_p">Realm:</p></td> <td><select class="custom-select" name="realm_sel" id="realm_sel">
									 <?php foreach ($this->wowrealm->getRealms()->result() as $charsMultiRealm) : ?>
										 <?php if ($charsMultiRealm->id == $atRealm): ?>
												<option value="<?= $charsMultiRealm->id ?>" selected><?= $this->wowrealm->getRealmName($charsMultiRealm->id) ?></option> 
										<?php else: ?>
												<option value="<?= $charsMultiRealm->id ?>"><?= $this->wowrealm->getRealmName($charsMultiRealm->id) ?></option> 
						
												<?php endif; ?>
									 <?php endforeach; ?>
						</select></td>
				</tr>
				<tr>
					<td><p class="mail_p">To:</p></td><td><input type="text"  name="searchChar" class="image-input" placeholder="Type in character name" value="<?=   $toCharacter; ?>"></td>
				</tr>
						
				<tr>
					<td><p class="mail_p">Message:</p></td>
				</tr>
		
			</table>	
<textarea class="textare" style="width: 100%; height: 300px"></textarea>
			<div class="center_stuff">
				<img src="http://wowragnaros.com:8888/application/modules/database/assets/images/tooltip/icons/money-gold.gif"  style="margin-right:2px"><input type="text" name="golds" class="image-input-small" style="width: 100%">
				<img src="http://wowragnaros.com:8888/application/modules/database/assets/images/tooltip/icons/money-silver.gif" style="margin-left: 5px;margin-right:2px"><input type="text" class="image-input-small" name="silvers" style="width: 100%">
				<img src="http://wowragnaros.com:8888/application/modules/database/assets/images/tooltip/icons/money-copper.gif" style="margin-left: 5px;margin-right:2px"><input type="text" class="image-input-small" name="coppers" style="width: 100%">
				
			</div>
			  <input type="text"  class="image-input_long"  id="itemname" placeholder="Enter item name">


			  <div id="autocomplete-results" class="search_result"></div>

<br><br>
			<div id="addedItemsContainer" class="added-items-container">
				  <!-- Dynamically added items will go here -->
			  </div>

			  <button class="button"  id="sendMail" style="margin:0px;">Send Mail!</button>
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
	// JavaScript and jQuery code
$(document).ready(function() {
		let selectedItem = null; // Variable to store selected item
	
		$('#itemname').on('input', function() {
			let query = $(this).val();
			let realm = $('#realm_sel').val(); // Assumed dropdown for realm
	
			if (query.length > 3) { // Check if user has typed more than 3 characters
				let apiUrl;
	
				if (isInt(query)) {
					apiUrl = `http://wowragnaros.com:8888/api/v1/item/${query}/${realm}`;
				} else {
					apiUrl = `http://wowragnaros.com:8888/en/api/v1/search_itemsonly?q=${query}&realm=${realm}`;
				}
	
				$.ajax({
					url: apiUrl,
					type: 'GET',
					dataType: 'json',
					success: function(data) {
						$('#autocomplete-results').empty().show();
	
						if (data.result && data.result.length > 0) {
							data.result.forEach(function(item) {
								let color = qualityColors[item.Quality] || 'Green';
	
								$('#autocomplete-results').append(`
									<div class="autocomplete-item" data-id="${item.entry}" data-name="${item.name}" data-color="${item.Quality}" data-icon="${item.icon}" style="color: ${color};">
										<img src="http://wowragnaros.com:8888/application/modules/database/assets/images/icons/${item.icon}.png" alt="${item.name}" style="width: 20px; height: 20px; vertical-align: middle;">
										${item.name}
									</div>
								`);
							});
	
							$('.autocomplete-item').on('click', function() {
        let item = $(this).data(); // Store item data
        addItemToContainer(item); // Call function to add item to container
        $('#itemname').val(''); // Clear input field
        $('#autocomplete-results').hide(); // Hide results
							});
						} else {
							$('#autocomplete-results').append('<div class="autocomplete-item">No hits nor titts!</div>');
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.error('Feil: ' + textStatus);
					}
				});
			} else {
				$('#autocomplete-results').hide(); // Hide results if less than 4 characters
			}
		});
	
	
		$('#itemname').on('click', function()
		{
							$('#itemname').val(''); // Clear input field
		});

  $('#itemname').on('blur', function() {
        // Delay the hiding to allow click event to fire on autocomplete items
        setTimeout(() => {
            let results = $('#autocomplete-results').text().trim();
            if (results === "No hits nor titts!") {
                $('#autocomplete-results').hide();
            }
        }, 200);
    });
	
		$('#removeItem').on('click', function() {
			$('.added-item.selected').remove(); // Remove selected items
		});
	
		$(document).on('click', '.added-item', function() {
			$(this).toggleClass('selected'); // Toggle selection state
			
		});
	
		function isInt(value) {
			return !isNaN(value) && parseInt(Number(value)) == value && !isNaN(parseInt(value, 10));
		}
	});

	    function addItemToContainer(item) {
        let container = $('#addedItemsContainer');
        let existingItem = container.find(`.added-item[data-id="${item.id}"]`);

        if (existingItem.length === 0) 
		{
            let color = qualityColors[item.color] || 'Green';
			container.append(`
                <div class="added-item" data-id="${item.id}" data-icon="${item.icon}" style="color: ${item.color}; display: flex; align-items: center; justify-content: space-between;">
					<div style="display: flex; align-items: center;">
						<img src="http://wowragnaros.com:8888/application/modules/database/assets/images/icons/${item.icon}.png" alt="Icon" style="width: 30px; height: 30px; vertical-align: middle;">
						<p style="margin: 0px; color: ${color}; padding-left: 10px;">${item.name}</p>
					</div>
					<button class="button_remove" onclick="removeItem(this)"></button>
				</div>
            `);
        }
    }
function removeItem(button) {
		$(button).closest('.added-item').remove();
	}
</script>

<style>
	
	.button_remove
	{
		background: url('/del.PNG');
		border: none;
		outline: none;
		vertical-align: revert-layer;
		height: 32px;
		width: 32px;
		margin: 0px;

	}
	
	.added-item {
		display: flex;
		align-items: center;
		justify-content: space-between; /* Plasserer elementene til venstre og høyre */
		padding: 1px;
		margin-bottom: 3px;
		border: 1px solid green;
		border-radius: 0px;
		cursor: pointer;
	}
	
	.added-item img {
		margin-right: 10px;
	}
	

	.button_remove:hover {
		font-family: 'FRIZQT';
		text-align: center;
		color: #ffffff;
		text-shadow: 0 0 8px #000, 0 0 8px #000, 0 0 8px #000, 0 0 8px #000, 0 0 8px #000;
	
		background: url('/del_sel.PNG') no-repeat top;
	}


		.custom-select {
			
			padding-left: 10px; /* Gir litt plass på venstre side for tekst */
			padding-right: 20px;
			padding-top: 2px;
			height: 36px;
			border: 0px;
			font-size: 12px;
			font-weight: bold;
			width: 100%;
			background-image: url('/searchbar2.png'); /* URL til bakgrunnsbilde */
			background-size: cover; /* Sørger for at bildet dekker hele bakgrunnen */

			-webkit-appearance: none; /* Fjerner standard utseende i WebKit-baserte nettlesere */
			-moz-appearance: none; /* Fjerner standard utseende i Mozilla-baserte nettlesere */
			appearance: none; /* Fjerner standard utseende */
			background-color: transparent; /* Gjør bakgrunnen gjennomsiktig */
			color: white; /* Setter tekstfarge for å sikre lesbarhet */
		}

    .image-input {
        background-image: url('/searchbar2.png'); /* Legg til din bilde-URL her */
        background-size: cover; /* Dekker hele input-feltet */
        height: 32px; /* Sett ønsket høyde */
        border: none; /* Fjerner standard kant */
		font-size: 12px;
		color: white;
        padding-left: 10px; /* Gir litt plass på venstre side for tekst */
		padding-right: 20px;
		padding-top: 2px;
		font-weight: bold;
    }

    .image-input-small {
        background-image: url('/searchsmall.png'); /* Legg til din bilde-URL her */
        background-size: cover; /* Dekker hele input-feltet */
        height: 32px; /* Sett ønsket høyde */
        border: none; /* Fjerner standard kant */
		font-size: 12px;
		color: white;
        padding-left: 10px; /* Gir litt plass på venstre side for tekst */
		padding-right: 20px;
		padding-top: 4px;
		font-weight: bold;
    }


	.image-input_long {
        background-image: url('/searchbar2.png'); /* Legg til din bilde-URL her */
        background-size: cover; /* Dekker hele input-feltet */
        height: 32px; /* Sett ønsket høyde */
        border: none; /* Fjerner standard kant */
		font-size: 12px;
		color: white;
        padding-left: 10px; /* Gir litt plass på venstre side for tekst */
		padding-right: 20px;
		padding-top: 2px;
		font-weight: bold;
	}
	.mail_p
	{
		font-size: : 30px;
		font-weight: bold;
		color: yellow;
		margin: 3px;
		
	}

	.button {
    font-family: 'FRIZQT';
    font-size: 23px;
    text-align: center;
    color: #ffc700;
    text-shadow: 0 0 8px #000, 0 0 8px #000, 0 0 8px #000, 0 0 8px #000, 0 0 8px #000;

    background: url('/button_idle.png') no-repeat top;
    width: 270px;
    height: 71px;
    border: none;
    outline: none;
    display: block;
    margin-top: -25px;
    transform: scale(0.8);
    filter: drop-shadow(0px 0px 8px #000);
    z-index: 2;
	cursor: url('/Point.png'), auto;
}

.button:hover {
    font-family: 'FRIZQT';
    text-align: center;
    color: #ffffff;
    text-shadow: 0 0 8px #000, 0 0 8px #000, 0 0 8px #000, 0 0 8px #000, 0 0 8px #000;

    background: url('/button_hover.png') no-repeat top;
}

.button:active {
    font-family: 'FRIZQT';
    font-size: 22px;
    text-align: center;
    color: #ffffff;
    text-shadow: 0 0 8px #000, 0 0 8px #000, 0 0 8px #000, 0 0 8px #000, 0 0 8px #000;

    background: url('/button_pressed.png') no-repeat top;
}
	
	.textare {
	        background-image: url('/txtbg.png'); /* Legg til din bilde-URL her */
			background-size: cover;
			color: white;
			font-weight: bold;
	}

	.mail_data_info {

		width: 100%;
	}
	.sidebyside_div {
	background-image: url('/background.PNG');
	background-size: fill;
		float: left;
		width: 285px;
		padding: 20px;
		max-width: 500px;
		

	}
	.main_mailbox_div {
		border-spacing: 10px;
		height: 100%;
		width: 280px;
		border: 5x solid black;
		float: left;
	}
	
	.mailbox_items_div {
		border-spacing: 10px;
		height: 100%;
		width: 45%;
		border: 5x solid black;
		float: left;
	}
	
	.center_stuff {
		display: flex;
		justify-content: center; /* Center horizontally */
		align-items: center;     /* Center vertically */
		margin: 5px;
	}



	.itemselect {
		width: 100%;
		height: 100%;
	}

  
	#autocomplete-results {
		border: 1px solid #ccc;
		background-image: url('/universalbg.png');
		background-size: cover;
		position: absolute;
		width: 300px;
		max-height: 200px;
		overflow-y: auto;
		display: none;
		z-index: 1000; /* Ensure it is on top */
	}

	.autocomplete-item {
		padding: 5px;
		cursor: pointer;
		font-size: 13px;
		
	}

	.autocomplete-item:hover {
		background-color: #282928;
	}

	.added-items-container {
		border: 0px solid #ccc;
		height: 100%;
		width: 100%;
		font-size: 15px;
	
	}



	.added-item.selected {
		background-color: #282928; /* Highlight selected items */
	}

</style>

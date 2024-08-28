<section class="uk-section uk-section-xsmall uk-padding-remove slider-section">
    <div class="uk-background-cover header-height header-section"
         style="background-image: url('<?= base_url() . 'application/themes/yesilcms/assets/images/headers/' . HEADER_IMAGES[array_rand(HEADER_IMAGES)] . '.jpg'; ?>')"></div>
</section>
<section class="uk-section uk-section-xsmall main-section" data-uk-height-viewport="expand: true">
    <div class="uk-container">
        <div class="uk-grid uk-grid-medium uk-margin-small" data-uk-grid>
		<?php
		
			//echo "Realm ID result: " . $realm;
		?>
            <div class="uk-width-3-3@s">
                <article class="uk-article">
                    <div class="uk-card uk-card-default uk-card-body uk-margin-small">
                        <?= form_open('armory/result', array('id' => "searcharmoryForm", 'method' => "get")); ?>
                        <div class="uk-margin">
                            <div class="uk-form-controls uk-light">
                                <div class="uk-inline uk-width-1-1">
                                    <h2 class="uk-text-center">Armory Search</h2>
                                    <table class="uk-table uk-table-small uk-table-responsive">
                                        <tr>
                                            <td><input class="uk-input" style="display:inline;" id="search"
                                                       name="search" type="text" minlength="2"
                                                       placeholder="Search by Player Name or Guild Name" value="<?= $search ?>" required></td>
                                            <td><select class="uk-inline uk-input minimal" style="display:inline;"
                                                        id="realm"
                                                        name="realm">
                                                    <?php 
													
													if ($realm == "ALL") 
													{
														echo '<option value = "ALL" selected>All Realms</option>';
													}
													else
													{
														echo '<option value = "ALL">All Realms</option>';
													}

													foreach ($realms as $realmInfo): 
													{
														if ($realmInfo->id == $realm) 
														{
															echo '<option value="' .$realmInfo->id . '" selected>'.$this->wowrealm->getRealmName($realmInfo->id) . '</option>';
														}
														else
														{
															echo '<option value="' .$realmInfo->id . '">'.$this->wowrealm->getRealmName($realmInfo->id) . '</option>';
														}
														
													}
													
													?>
                                                        
                                                    <?php endforeach; ?>
                                                </select></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <input class="uk-button uk-button-default uk-width-1-1" type="submit" value="search">
                        <?= form_close(); ?>
                        <?php if (empty($_GET['search'])) {
                            echo "\n<br/>There are no recent searches.";
                        } else {
                            echo "\n<br/>Recent search: <span class=\"system\">[" . $_GET['search'] . "]</span>";
                        } ?>
                    </div>
                </article>
            </div>
        </div>
    </div>

    <div class="uk-container">
        <div class="uk-grid uk-grid-medium uk-margin-small" data-uk-grid>
            <div class="uk-width-3-3@s">
                <article class="uk-article">
                    <div class="uk-card uk-card-default uk-card-body uk-margin-small">
                        <h3 class="uk-text-center">Search results for '<i><?= $search ?></i>'</h3>
                        <div class="uk-button-group uk-width-1-1">
                            <button id="PlayerInit"
                                    class="uk-button uk-button-default uk-width-1-2 uk-margin-small-right"
                                    style="display:inline;"
                                    onclick="opentab('Player')">Players
                            </button>
                            <button class="uk-button uk-button-default uk-width-1-2" style="display:inline;"
                                    onclick="opentab('Guilds')">Guilds
                            </button>
                        </div>
                     





<div id="Player" class="tab uk-margin-small-top">
    <?php if ($realm == "ALL"): ?>
        <?php
		
        $getRealms = $this->wowrealm->getRealms()->result();
        $allPlayers = []; 

        foreach ($getRealms as $c_realm) {
            $currentRealm = $this->wowrealm->getRealmConnectionData($c_realm->id);
            $players = $this->armory_model->searchChar($currentRealm, $search);

            if ($players->num_rows() > 0) {
                // Legger til alle spillere i arrayet og inkluderer realm-informasjonen
                foreach ($players->result() as $player) {
                    $player->realm = $c_realm->id;  // Legg til realm-id i player-objektet
                    $allPlayers[] = $player;
                }
            }
        }

        if (empty($allPlayers)): ?>
            <div class="uk-alert-danger uk-margin-center" style="text-align: center" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><i class="fas fa-exclamation-triangle"></i> <i>No players were found in any realm matching your search <b>'<?= $search; ?>'</b>. Please try a different search term.</i> <i style="align-items: right" class="fas fa-exclamation-triangle"></i></p>
            </div>
			</div>
        <?php else: ?>
            <div class="uk-overflow-auto uk-margin-small">
                <table class="uk-table dark-table uk-table-divider uk-table-small uk-table-middle">
                    <thead>
                    <tr>
                        <th class="uk-table-expand uk-text-center">Player</th>
                        <th class="uk-table-expand uk-text-center">Level</th>
                        <th class="uk-table-expand uk-text-center">Total Played Time</th>
                        <th class="uk-table-expand uk-text-center">Faction</th>
                        <th class="uk-table-expand uk-text-center">Race</th>
                        <th class="uk-table-expand uk-text-center">Class</th>
                        <th class="uk-table-expand uk-text-center">Realm</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allPlayers as $player): ?>
                        <tr class="pg-td">
                            <td class="uk-table-expand uk-text-center"><a
                                    href="<?= base_url() . 'armory/character/' . $player->realm . '/' . $player->guid ?>"><?= $player->name ?></a>
                            </td>
                            <td class="uk-table-expand uk-text-center"><?= $player->level ?></td>
                            <td class="uk-table-expand uk-text-center"><?= secondsToTime($player->totaltime) ?></td>
                            <td class="uk-table-expand uk-text-center"><img align="center"
                                                                            src="<?= base_url('assets/images/factions/' . $this->wowgeneral->getFactionIcon($player->race)); ?>"
                                                                            width="50" height="50"
                                                                            title="<?= $this->wowgeneral->getRaceName($player->race); ?>"
                                                                            alt="<?= $this->wowgeneral->getRaceName($player->race); ?>">
                            </td>
                            <td class="uk-table-expand uk-text-center"><img align="center"
                                                                            class="uk-border-circle"
                                                                            src="<?= base_url() . 'application/modules/armory/assets/images/characters/' . getAvatar($player->class, $player->race, $player->gender, $player->level); ?>"
                                                                            width="50" height="50"
                                                                            title="<?= $this->wowgeneral->getRaceName($player->race); ?>"
                                                                            alt="<?= $this->wowgeneral->getRaceName($player->race); ?>">
                            </td>
                            <td class="uk-table-expand uk-text-center"><img align="center"
                                                                            src="<?= base_url('assets/images/class/' . $this->wowgeneral->getClassIcon($player->class)); ?>"
                                                                            width="50" height="50"
                                                                            title="<?= $this->wowgeneral->getClassName($player->class); ?>"
                                                                            alt="<?= $this->wowgeneral->getClassName($player->class); ?>">
                            </td>
                            <td class="uk-table-expand uk-text-center">
                                <?php
                                $img_realm = $this->wowrealm->isTbc($player->realm) ? "wow-tbc.png" : "wow-vanilla.png";
                                ?>
                                <img align="center"
                                     src="<?= base_url('/assets/images/forums/wow-icons/' . $img_realm); ?>"
                                     width="50" height="50"
                                     title="<?= $this->wowrealm->getRealmName($player->realm); ?>"
                                     alt="<?= $this->wowrealm->getRealmName($player->realm); ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div></div>
        <?php endif; ?>
    <?php else: ?>
        <?php
        $currentRealm = $this->wowrealm->getRealmConnectionData($realm);
        $searchArr = $this->armory_model->searchChar($currentRealm, $search);
        $tHeadOnce = 0;

        if ($searchArr->num_rows() == 0): ?>
            <div class="uk-alert-danger uk-margin-center" style="text-align: center" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><i class="fas fa-exclamation-triangle"></i> <i>No player was found as a result of
                        your search on the realm </i><b>"<?= $this->wowrealm->getRealmName($realm); ?>"</b><i>, please review your search or try another realm</i> <i style="align-items: right" class="fas fa-exclamation-triangle"></i></p>
            </div>
        <?php endif;

        foreach ($searchArr->result() as $player):
            if ($tHeadOnce == 0): ?>
                <div class="uk-overflow-auto uk-margin-small">
                    <table class="uk-table dark-table uk-table-divider uk-table-small uk-table-middle">
                        <thead>
                        <tr>
                            <th class="uk-table-expand uk-text-center">Player</th>
                            <th class="uk-table-expand uk-text-center">Level</th>
                            <th class="uk-table-expand uk-text-center">Total Played Time</th>
                            <th class="uk-table-expand uk-text-center">Faction</th>
                            <th class="uk-table-expand uk-text-center">Race</th>
                            <th class="uk-table-expand uk-text-center">Class</th>
                        </tr>
                        </thead>
            <?php endif; ?>
                        <tbody>
                        <tr class="pg-td">
                            <td class="uk-table-expand uk-text-center"><a
                                    href="<?= base_url() . 'armory/character/' . $realm . '/' ?><?= $player->guid ?>"><?= $player->name ?></a>
                            </td>
                            <td class="uk-table-expand uk-text-center"><?= $player->level ?></td>
                            <td class="uk-table-expand uk-text-center"><?= secondsToTime($player->totaltime) ?></td>
                            <td class="uk-table-expand uk-text-center"><img align="center"
                                                                            src="<?= base_url('assets/images/factions/' . $this->wowgeneral->getFactionIcon($player->race)); ?>"
                                                                            width="50" height="50"
                                                                            title="<?= $this->wowgeneral->getRaceName($player->race); ?>"
                                                                            alt="<?= $this->wowgeneral->getRaceName($player->race); ?>">
                            </td>
                            <td class="uk-table-expand uk-text-center"><img align="center"
                                                                            class="uk-border-circle"
                                                                            src="<?= base_url() . 'application/modules/armory/assets/images/characters/' . getAvatar($player->class, $player->race, $player->gender, $player->level); ?>"
                                                                            width="50" height="50"
                                                                            title="<?= $this->wowgeneral->getRaceName($player->race); ?>"
                                                                            alt="<?= $this->wowgeneral->getRaceName($player->race); ?>">
                            </td>
                            <td class="uk-table-expand uk-text-center"><img align="center"
                                                                            src="<?= base_url('assets/images/class/' . $this->wowgeneral->getClassIcon($player->class)); ?>"
                                                                            width="50" height="50"
                                                                            title="<?= $this->wowgeneral->getClassName($player->class); ?>"
                                                                            alt="<?= $this->wowgeneral->getClassName($player->class); ?>">
                            </td>
                            
                        </tr>
                        <?php $tHeadOnce = 1; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
		</div>
        <?php endif; ?>



<div id="Guilds" class="tab uk-margin-small-top" style="display:none">
    <?php if ($realm == "ALL"): ?>
        
		<?php
		
        $getRealms = $this->wowrealm->getRealms()->result();
        $allGuilds = []; 


        foreach ($getRealms as $c_realm) {
            $currentRealm = $this->wowrealm->getRealmConnectionData($c_realm->id);
            $guilds = $this->armory_model->searchGuild($currentRealm, $search);

            if ($guilds->num_rows() > 0) {
                // Legger til alle guilds i arrayet og inkluderer realm-informasjonen
                foreach ($guilds->result() as $guild) {
                    $guild->realm = $c_realm->id;  // Legg til realm-id i guild-objektet
                    $allGuilds[] = $guild;
                }
            }
        }

        if (empty($allGuilds)): ?>
            <div class="uk-alert-danger uk-margin-center" style="text-align: center" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><i class="fas fa-exclamation-triangle"></i> <i>No guilds were found in any realm matching your search <b>'<?= $search; ?>'</b>. Please try a different search term.</i> <i style="align-items: right" class="fas fa-exclamation-triangle"></i></p>
            </div>
        <?php else: ?>
            <div class="uk-overflow-auto uk-margin-small">
                <table class="uk-table dark-table uk-table-divider uk-table-small uk-table-middle">
                    <thead>
                    <tr>
                        <th class="uk-table-expand uk-text-center">Guild Name</th>
                        <th class="uk-table-expand uk-text-center">Guild Message</th>
                        <th class="uk-table-expand uk-text-center">Realm</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allGuilds as $guild): ?>
                        <tr class="pg-td">
                            <td class="uk-table-expand uk-text-center"><a
                                    href="<?= base_url() . 'armory/guild/' . $guild->realm . '/' . $guild->guildid ?>"><?= $guild->name ?></a>
                            </td>
                            <td class="uk-table-expand uk-text-center"><?= $guild->motd ?></td>
                            <td class="uk-table-expand uk-text-center">
                                <?php
                                $img_realm = $this->wowrealm->isTbc($guild->realm) ? "wow-tbc.png" : "wow-vanilla.png";
                                ?>
                                <img align="center"
                                     src="<?= base_url('/assets/images/forums/wow-icons/' . $img_realm); ?>"
                                     width="50" height="50"
                                     title="<?= $this->wowrealm->getRealmName($guild->realm); ?>"
                                     alt="<?= $this->wowrealm->getRealmName($guild->realm); ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?php
        $currentRealm = $this->wowrealm->getRealmConnectionData($realm);
        $searchArr = $this->armory_model->searchGuild($currentRealm, $search);

        if ($searchArr->num_rows() == 0): ?>
            <div class="uk-alert-danger uk-margin-center" style="text-align: center" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><i class="fas fa-exclamation-triangle"></i> <i>No guilds were found as a result of your search on the realm </i><b>"<?= $this->wowrealm->getRealmName($realm); ?>"</b><i>, please review your search or try another realm</i> <i style="align-items: right" class="fas fa-exclamation-triangle"></i></p>
            </div>
        <?php else: ?>
            <div class="uk-overflow-auto uk-margin-small">
                <table class="uk-table dark-table uk-table-divider uk-table-small uk-table-middle">
                    <thead>
                    <tr>
                        <th class="uk-table-expand uk-text-center">Guild Name</th>
                        <th class="uk-table-expand uk-text-center">Guild Message</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($searchArr->result() as $guild): ?>
                        <tr class="pg-td">
                            <td class="uk-table-expand uk-text-center"><a
                                    href="<?= base_url() . 'armory/guild/' . $realm . '/' . $guild->guildid ?>"><?= $guild->name ?></a>
                            </td>
                            <td class="uk-table-expand uk-text-center"><?= $guild->motd ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>





                    </div>
                </article>
            </div>
        </div>
    </div>
</section>
<script>
    document.getElementById('PlayerInit').focus();

    function opentab(tabname) {
        var i;
        var x = document.getElementsByClassName("tab");
        for (i = 0; i < x.length; i++) {
            x[i].style.display = "none";
        }
        document.getElementById(tabname).style.display = "block";
    }
</script>
<script>
    function SearchArmoryForm(e) {
        e.preventDefault();

        var search = $('#search').val();
        var realm = $('#realm').val();
        if (search == '') {
            $.amaran({
                'theme': 'awesome error',
                'content': {
                    title: '<?= $this->lang->line('notification_title_error'); ?>',
                    message: '<?= $this->lang->line('notification_title_empty'); ?>',
                    info: '',
                    icon: 'fas fa-times-circle'
                },
                'delay': 5000,
                'position': 'top right',
                'inEffect': 'slideRight',
                'outEffect': 'slideRight'
            });
            return false;
        }
        $.ajax({
            url: "<?= base_url($lang . '/armory/result'); ?>",
            method: "GET",
            data: {search, realm},
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
                    'delay': 5000,
                    'position': 'top right',
                    'inEffect': 'slideRight',
                    'outEffect': 'slideRight'
                });
            },
            success: function (response) {
                if (!response)
                    alert(response);

                if (response) {
                    $.amaran({
                        'theme': 'awesome ok',
                        'content': {
                            title: '<?= $this->lang->line('notification_title_success'); ?>',
                            message: 'Search performed..',
                            info: '',
                            icon: 'fas fa-check-circle'
                        },
                        'delay': 5000,
                        'position': 'top right',
                        'inEffect': 'slideRight',
                        'outEffect': 'slideRight'
                    });
                }
                $('#searcharmoryForm')[0].reset();
                window.location.replace("<?= base_url('armory/result'); ?>");
            }
        });
    }
</script>

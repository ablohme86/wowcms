<section class="uk-section uk-section-xsmall uk-padding-remove slider-section">
    <div class="uk-background-cover header-height header-section"
         style="background-image: url('<?= base_url() . 'application/themes/yesilcms/assets/images/headers/' . HEADER_IMAGES[array_rand(HEADER_IMAGES)] . '.jpg'; ?>')"></div>
</section>
<section class="uk-section uk-section-xsmall main-section" data-uk-height-viewport="expand: true">
    <div class="uk-container">
        <div class="uk-grid uk-grid-medium uk-margin-small" data-uk-grid>
            <div class="uk-width-3-3@s">
                <article class="uk-article">
                    <div class="uk-card uk-card-default uk-card-body uk-margin-small" style="align-text: center">
                        <?php
                        $currentRealm = $this->wowrealm->getRealmConnectionData($realmid);
         // Show 404 If realmID doesn't exists
         if (! $currentRealm) {
             redirect(base_url('404'), 'refresh');
         }
         foreach ($this->armory_model->getGuildInfo($currentRealm, $guildid)->result() as $guild): ?>
                            <center><h2 style="display:inline;"><b> &lt;<?= $guild->name ?>&gt;</b> of <?= $this->wowrealm->getRealmName($realmid) ?></h2><br><b style="display:inline;">
                                Message: </b><i style="display:inline;">"<?= $guild->motd ?>"</i></center>
                            <hr>
                        <?php endforeach; ?>
                        <h2 class="uk-text-center">Members</h2>
                        <div class="uk-overflow-auto uk-margin-small">
                            <table class="uk-table dark-table uk-table-divider uk-table-small">
                                <thead>
                                <tr>
                                    <th class="uk-table-expand uk-text-center">Name</th>
                                    <th class="uk-table-expand uk-text-center">Level</th>
                                    <th class="uk-table-expand uk-text-center">Race</th>
                                    <th class="uk-table-expand uk-text-center">Class</th>
                                </tr>
                                <tbody>
                                <?php foreach ($this->armory_model->getGuildMembers($currentRealm, $guildid)->result() as $player): ?>
                                    <tr>
                                        <td class="uk-text-center"><a
                                                        href="<?= base_url() . 'armory/character/' . $realmid . '/' ?><?= $player->guid ?>"><?= $player->name ?></a>
                                            </td>
                                        <td class="uk-text-center"><?= $player->level ?></td>
                                        <td class="uk-table-expand uk-text-center"><img align="center"
                                                                                        class="uk-border-circle"
                                                                                        src="<?= base_url() . 'application/modules/armory/assets/images/characters/' . getAvatar($player->class, $player->race, $player->gender, $player->level); ?>"
                                                                                        width="40" height="40"
                                                                                        title="<?= $this->wowgeneral->getRaceName($player->race); ?>"
                                                                                        alt="<?= $this->wowgeneral->getRaceName($player->race); ?>">
                                        </td>
                                        <td class="uk-text-center"><img align="center"
                                                                        src="<?= base_url('assets/images/class/' . $this->wowgeneral->getClassIcon($player->class)); ?>"
                                                                        width="40" height="40"
                                                                        title="<?= $this->wowgeneral->getClassName($player->class); ?>"
                                                                        alt="<?= $this->wowgeneral->getClassName($player->class); ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                </thead>
                            </table>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

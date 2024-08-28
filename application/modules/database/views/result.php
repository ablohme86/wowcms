<?php
        $realm = $this->input->get('realm');    
        if ($realm == "")
        {
            $realm = $this->input->post('realm');
        }
        $data = $this->wowrealm->getRealm($realm)->row_array();

        if ($data < 1)
        {

                redirect(base_url('404'), 'refresh');
          
        }
        
		$realms = $this->wowrealm->getRealms()->result();
   
   echo "Do we stuck here??";

?>
<link rel="stylesheet" href="<?= base_url() . 'application/modules/database/assets/css/database.css'; ?>"/>
<section class="uk-section uk-section-xsmall uk-padding-remove slider-section">
    <div class="uk-background-cover header-height header-section"
         style="background-image: url('<?= base_url() . 'application/themes/yesilcms/assets/images/headers/' . HEADER_IMAGES[array_rand(HEADER_IMAGES)] . '.jpg'; ?>')"></div>
</section>
<section class="uk-section uk-section-xsmall main-section" data-uk-height-viewport="expand: true">
    <div class="uk-container">
        <div class="uk-grid uk-grid-medium uk-margin-small" data-uk-grid>
            <div class="uk-width-3-3@s">
                <article class="uk-article">
                    <div class="uk-card uk-card-default uk-card-body uk-margin-small">
                        <div class="uk-margin">
                            <div class="uk-form-controls uk-light">
                                <div class="uk-inline uk-width-1-1">
                                    <h2 class="uk-text-center">Database Search</h2>
                                    <table class="uk-table uk-table-small uk-table-responsive">
                                        <?= form_open('database/result', array('id' => "searchDatabase",'realm' => $realm, 'method' => "get")); ?>
                                        <tr>
                                            <td>
                                                <input class="uk-input" style="display:inline;" id="search"
                                                       name="search" type="text" minlength="3" autocomplete="off"
                                                       placeholder="Search Item & Spell by Name or ID" required>
                                            </td>
                                            <td class="uk-width-1-4">
                                          <select class="uk-inline uk-input minimal" style="display:inline;"
                                                  id="realm"
                                                  name="realm">
                                          
                                              
                                              <?php foreach ($realms as $realmInfo): ?>
                                                <?php if ($realmInfo->id == $realm): ?>
                                                    <option value="<?= $realmInfo->id ?>" selected><?= $this->wowrealm->getRealmName($realmInfo->id); ?></option>

                                                    <?php else: ?>
                                                        <option value="<?= $realmInfo->id ?>"><?= $this->wowrealm->getRealmName($realmInfo->id); ?></option>

                                                    <?php endif; ?>
                                              
                                              
                                              <?php endforeach; ?>
                                          </select></td>
                                        </tr>
                                        <?= form_close(); ?>

                                    </table>
                                </div>
                            </div>
                        </div>
                        <input class="uk-button uk-button-default uk-width-1-1" type="submit" form="searchDatabase" value="search">
                        <br>
              
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
                        <h3 class="uk-text-center">Search Results</h3>
                        <?php if ($items || $spells) : ?>
                            <ul class="uk-tab" data-uk-tab="{connect:'#tab-id'}">
                                <?php if ($items) : ?>
                                    <li><a href="">Item (<?= count($items) ?>)</a></li>
                                <?php endif;
                                if ($spells) : ?>
                                    <li><a href="">Spell (<?= count($spells) ?>)</a></li>
                                <?php endif; ?>
                            </ul>
                            <ul id="tab-id" class="uk-switcher uk-margin">
                                <?php if ($items) : ?>
                                    <li>
                                        <div class="uk-overflow-auto uk-margin-small">
                                            <table class="uk-table uk-table-small uk-table-divider uk-table-hover uk-table-middle yesilcms-table">
                                                <thead>
                                                <tr>
                                                    <th class="uk-preserve-width">Name</th>
                                                    <th class="uk-preserve-width uk-text-center">Level</th>
                                                    <th class="uk-preserve-width uk-text-center">Req</th>
                                                    <th class="uk-preserve-width uk-text-center">Slot</th>
                                                    <th class="uk-preserve-width uk-text-center">Type</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($items as $item) : ?>
                                                    <tr>
                                                        <td style="min-width: 250px;">
                                                    <span class="iconmedium">
                                                        <ins class="yesilcms-lazy" style="background-image: url('<?= base_url() . 'application/modules/database/assets/images/icons/' . $item['icon'] ?>.png');"></ins>
                                                        <del></del>
                                                    </span>
                                                            <a href="<?= base_url($lang) ?>/item/<?= $item['entry'] ?>/<?= $realm ?>" data-item="item=<?= $item['entry'] ?>" data-realm='<?= $realm ?>' data-patch='<?= 10 ?>'><span class="q<?= $item['Quality'] ?>"><?= $item['name'] ?></span></a>
                                                        </td>
                                                        <td class="uk-text-center"><?= $item['ItemLevel'] ?></td>
                                                        <td class="uk-text-center"><?= $item['RequiredLevel'] ?></td>
                                                        <td class="uk-text-center"><?= itemInventory($item['InventoryType']) ?></td>
                                                        <td class="uk-text-center"><?= itemSubClass($item['class'], $item['subclass']) ?? '' ?> </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </li>
                                <?php endif; ?>
                                <?php if ($spells) : ?>
                                    <li>
                                        <div class="uk-overflow-auto uk-margin-small">
                                            <table class="uk-table uk-table-small uk-table-divider uk-table-hover uk-table-middle yesilcms-table">
                                                <thead>
                                                <tr>
                                                    <th class="uk-table-shrink">Name</th>
                                                    <th class="uk-text-center">Level</th>
                                                    <th class="uk-text-center">School</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($spells as $spell) : ?>
                                                    <tr>
                                                        <td style="min-width: 250px;">
                                                    <span class="iconmedium">
                                                        <ins class="yesilcms-lazy" style="background-image: url('<?= base_url() . 'application/modules/database/assets/images/icons/' . $spell['icon'] ?>.png');"></ins>
                                                        <del></del>
                                                    </span>
                                                            <a href="<?= base_url($lang) ?>/spell/<?= $spell['Id'] ?>/<?= $realm ?>" data-spell="spell=<?= $spell['Id'] ?>" data-realm='<?= $realm ?>' data-patch='<?= 10 ?>'><?= $spell['SpellName'] ?></a>
                                                            <?php if ($spell['Rank1']) : ?>
                                                                <div class="srank"><?= $spell['Rank1'] ?></div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="uk-text-center"><?= $spell['BaseLevel'] ?></td>
														<?php if ($this->wowrealm->isTbc($realm) == 0) 
														{
															printf('<td class="uk-text-center">%s</td>',schoolType($spell['School']));
														}
														else
														{
															// TBC uses SchoolMask
															printf('<td class="uk-text-center">%s</td>',schoolType($spell['SchoolMask']));
														}

														?>

                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        <?php else : ?>
                            <div class="uk-alert-danger uk-text-center " uk-alert>
                                <p>We searched all over Azeroth, but unfortunately we couldn't find what you are looking for..:(</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>
<div id="tooltip" class="tooltip yesilcms-tooltip"></div>
<script type="text/javascript" src="<?= base_url() . 'application/modules/database/assets/js/tooltip.js'; ?>"></script>
<script type="text/javascript" src="<?= base_url() . 'application/modules/database/assets/js/jquery.dataTables.min.js'; ?>"></script>
<script type="text/javascript" src="<?= base_url() . 'application/modules/database/assets/js/dataTables.uikit.min.js'; ?>"></script>
<script type="text/javascript" src="/jquery.lazy.min.js"></script>
<script type="text/javascript" src="<?= base_url() . 'application/modules/database/assets/js/bootstrap3-typeahead.min.js'; ?>"></script>
<script>
    const baseURL = "<?= base_url($lang); ?>";
    const imgURL = "<?= base_url() . 'application/modules/database/assets/images/icons/'; ?>";
    let csrf_token = "<?= $this->security->get_csrf_hash() ?>";
    $(document).ready(function () {
        $(function () {
            $('.yesilcms-lazy').lazy();
        });
        $('table.yesilcms-table').DataTable({
            order: [[0, 'asc']]
        });

        if (jQuery('input#search').length > 0) {
            jQuery('input#search').typeahead({
                autoSelect: false,
                minLength: 3,
                delay: 333,

                displayText: function (item) {
                    console.log("inside displayText...");
                    type = "Item";

                    if (typeof item.quality === 'undefined') {
                        item.quality = 11; //spell
                    }

                    if (Object.keys(item).length == 8) {
                        type = 'spell';
                    } else {
                        type = 'item';
                    }

                    let isnum = /^\d+$/.test(jQuery('input#search').val());

                    res = '<div class="live-search-icon" style="background-image: url(<?= base_url() . 'application/modules/database/assets/images/icons/'?>' + item.icon + '.png)">';
                    res += '<span class="bg">';
                    res += '<a href="' + type + '/' + item.entry +  '/' + realm.id + '" class="q' + item.Quality + '" data-' + type + '="' + type + '=' + item.entry + '" data-patch=10><span>' + item.name + '</span><i>' + type + '</i></a>';
                    res += '</span></div>';
                    return res;
                },
                afterSelect: function (item) {
                    console.log("we have afterSelect");
                    this.$element[0].value = item.name;
                    window.location.href = baseURL + '/' + type + '/' + item.entry
                },
                source: function (query, process) 
                {
                    console.log("Searching server with; " + query + " and realm: " + realm.value);
                    jQuery.ajax({
                        url: baseURL + "/api/v1/search/db",
                        data: {q: query, patch: realm,p: realm.value,realm: realm.value, <?= $this->security->get_csrf_token_name() ?>: csrf_token},
                        dataType: "json",
                        type: "POST",
                        success: function (data) 
                        {
                            csrf_token = data.token
                            process(data.result)
                            TooltipExtended.initialize()
                        },
                        error: function (result) { //for next request
                            csrf_token = result.responseJSON.token;
                        }
                    })
                }
            });
        }
    });
</script>
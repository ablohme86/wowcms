
<link rel="stylesheet" href="<?= base_url() . 'application/modules/database/assets/css/database.css'; ?>"/>
<section class="uk-section uk-section-xsmall uk-padding-remove slider-section">
    <div class="uk-background-cover header-height header-section" style="background-image: url('<?= base_url() . 'application/themes/yesilcms/assets/images/headers/' . HEADER_IMAGES[array_rand(HEADER_IMAGES)] . '.jpg'; ?>')"></div>
</section>
<section class="uk-section uk-section-xsmall main-section" data-uk-height-viewport="expand: true">
    <div class="uk-container">
	<?php 
	
		$realmname = $this->wowrealm->getRealmName($realmid);
		
		
	
	?>
    
        <?php if ($item) : ?>
            <div class="text">
                <h1><?= $item['name'] ?></h1>
            </div>
            <div class="uk-text-left" uk-grid>
                <div class="uk-width-auto@m">
                    <div style="float: left; padding-right:10px; margin-top:-2px">
                        <div class="iconlarge">
                            <ins class="yesilcms-lazy" style="background-image: url('<?= base_url() . 'application/modules/database/assets/images/icons/' . $item['icon'] ?>.png');"></ins>
                            <del></del>
                            <?php if ($item['stackable'] > 1) : ?>
                                <span class="stackable"><?= $item['stackable'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="uk-card uk-card-default uk-card-body" style="max-width:350px; padding: 0">
                        <div class="yesilcms-tooltip">
                            <table>
                                <tbody>
                                <tr>
                                    <td>
                                        <table style="width: 100%;">
                                            <tbody>
                                            <tr>
                                                <td><b class="q<?= $item['Quality'] ?>"><?= $item['name'] ?></b><br/>
                                                    <?php if ($item['ItemLevel'] > 0) : ?>
                                                        <span class="q">Item Level <?= $item['ItemLevel'] ?> </span><br/>
                                                    <?php endif; ?>
                                                    <?= $item['bonding'] > 0 ? itemBonding($item['bonding']) . '<br/>' : '' ?>
                                                    <?= (! empty($item['maxcount']) && strlen($item['maxcount']) > 0) ? itemCount($item['maxcount']) : '' ?>
                                                    <table style="width: 100%;">
                                                        <tbody>
                                                        <tr>
                                                            <td><?= $item['inv_type'] ?? '' ?></td>
                                                            <th><?= $item['isubclass'] ?? '' ?></th>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                    <?php if (isWeapon($item['class'])) :
                                                        foreach ($item['dmg_list'] as $key => $dmg) :
                                                            if ($key === 0) : ?>
                                                                <table style="width: 100%;">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td><?= $dmg ?></td>
                                                                        <th>Speed <?= number_format($item['delay'] / 1000, 2) ?></th>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            <?php else : ?>
                                                                <?= $dmg ?><br/>
                                                            <?php endif;
                                                        endforeach;
                                                        ?>
                                                        (<?= $item['dps'] ?>)<br/>
                                                    <?php endif;
                                                    if ($item['stats']) :
                                                        foreach ($item['stats'] as $istat) : ?>
                                                            <span><?= $istat ?></span><br/>
                                                        <?php endforeach;
                                                    endif;
                                                    if ($item['resistances']) :
                                                        foreach ($item['resistances'] as $iresistance) : ?>
                                                            <span><?= $iresistance ?></span><br/>
                                                        <?php endforeach;
                                                    endif;
                                                    if ($item['RandomProperty']) : ?>
                                                        <span class="q2">&lt;Random Enchantment&gt;</span><br/>
                                                    <?php endif;
                                                    if ($item['MaxDurability']) : ?>
                                                        Durability <?= $item['MaxDurability'] ?> / <?= $item['MaxDurability'] ?><br/>
                                                    <?php endif;
                                                    if ($dur = $item['Duration']) : ?>
                                                        Duration: <?= formatTime(abs($dur) * 1000) . ($item['rtduration'] ? ' (real time)' : '') ?><br/>
                                                    <?php endif;
                                                    if ($item['startquest'] > 1) : ?>
                                                        This Item Begins a Quest <br/>
                                                    <?php endif;
                                                    if ($item['RequiredLevel'] > 1) : ?>
                                                        Requires Level <?= $item['RequiredLevel'] ?> <br/>
                                                    <?php endif;
                                                    if ($item['requiredspell'] > 0) : ?>
                                                        Requires <?= $item['req_spell'] ?><br/>
                                                    <?php endif;
                                                    if ($item['RequiredSkill'] > 0) : ?>
                                                        <?= requiredSkill($item['RequiredSkill'], $item['RequiredSkillRank']) ?><br/>
                                                    <?php endif;
                                                    if ($item['AllowableClass'] > 0 && $item['allowed_classes']) : ?>
                                                        Classes: <?= $item['allowed_classes'] ?> <br/>
                                                    <?php endif;
                                                    if ($item['AllowableClass'] > 0 && $item['allowed_races']) : ?>
                                                        Races: <?= $item['allowed_races'] ?> <br/>
                                                    <?php endif;
                                                    if ($item['requiredhonorrank']) : ?>
                                                        <span class="q10">Requires <?= getRankByFaction($item['name'], $item['requiredhonorrank']) ?> </span><br/>
                                                    <?php endif;
                                                    if ($item['RequiredReputationFaction']) : ?>
                                                        <span class="q10">Requires <?= $item['req_fact_rep'] .
                                                                                       ' - ' . getRepRank($item['RequiredReputationFaction']) ?> </span><br/>
                                                    <?php endif;
                                                    if ($item['openable']) : ?>
                                                        <span class="q2">&lt;Right Click To Open&gt;</span><br/>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <?php if ($item['item_spells_trigger']) :
                                            $itemSpells = array_keys($item['item_spells_trigger']); ?>
                                            <table style="width: 100%;">
                                                <?php if ($itemSpells) : ?>
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <?php foreach ($itemSpells as $key => $sid) : ?>
                                                                <span class="q2"><?= $item['trigger_text'][$item['item_spells_trigger'][$sid][0]] ?> <a
                                                                            href="<?= base_url($lang) ?>/spell/<?= $sid ?>/<?= $realmid ?>" data-realm='<?= $realmid ?>' data-spell="spell=<?= $sid ?>"
                                                                            data-patch='<?= dataPatch($patch) ?>'><?= $this->Database_model->getSpellDetails($sid, $realmid) ?></a> <?= $item['item_spells_trigger'][$sid][1] ?></span>
                                                                <br/>
                                                            <?php endforeach; ?>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                <?php endif; ?>
                                            </table>
                                        <?php endif;
                                        if ($item['itemset']) : ?>
                                            <div id="tooltip-item-set" style="padding-top: 10px;">
                                                <div class="q" id="tooltip-item-set-name"><?= $item['itemset_set_name'] ?> (<span id="tooltip-item-set-count">0</span>/<?= count($item['itemset_item_list']) ?>)</div>
                                                <div id="tooltip-item-set-pieces" style="padding-left: .6em">
                                                    <div class="q0 indent">
                                                        <?php foreach ($item['itemset_item_list'] as $item_id => $piece) : ?>
                                                            <span class="item-set-piece" data-item="item=<?= $item_id ?>" data-realm='<?= $realmid ?>' data-patch='<?= dataPatch($patch) ?>'> <a href="<?= base_url($lang) ?>/item/<?= $item_id ?>/<?= $realmid ?>"><?= $piece ?></a> </span> <br/>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <div id="tooltip-item-set-bonuses" style="padding-top: 10px;">
                                                    <div class="q0">
                                                        <?php foreach ($item['itemset_set_list'] as $threshold => $set) : ?>
                                                            <span class="item-set-bonus">(<?= $threshold ?>) Set-bonus: <a href="<?= base_url($lang) ?>/spell/<?= $set[1] ?>/<?= $realmid ?>" data-spell="spell=<?= $set[1] ?>" data-realm='<?= $realmid ?>' data-patch='<?= 10 ?>'><span
                                                                            id="set-bonus-text"><?= $set[0] ?></span></a> </span> <br/>


                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif;
                                        if ($item['description'] !== '') : ?>
                                            <span class="q"><?= $item['description'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if ($item['obtainable']) : ?>
                        <div class="uk-padding-small"></div>
                        <span class="q10">This item is not available to players.</span>
                    <?php endif; ?>
                </div>
                <div class="uk-width-expand@m">
                    <!--<div class="uk-card uk-card-default uk-card-body">TODO: 1-3 Either keep it hidden or put some data here</div>-->
                </div>
                <div class="uk-width-auto@m uk-align-right@m">
                    <div class="uk-card uk-card-default uk-card-body">
                        <table class="infobox">
                            <tbody>
                            <tr>
                                <th>Quick Facts</th>
                            </tr>
                     
                            <tr>
                                <td>
                                    <div>
                                        <ul class="first last">
                                            <?php if ($item['BuyPrice']) : ?>
                                                <li>
                                                    <div>Item Level: <?= $item['ItemLevel']; ?></div>
                                                </li>
                                            <?php endif; ?>
                                            <li>
                                                <div>Icon: <a href="<?= base_url() . 'application/modules/database/assets/images/icons/' . $item['icon'] ?>.png" target="_blank"><span class="iconsmall"><ins
                                                                    class="yesilcms-lazy" style="background-image: url('<?= base_url() . 'application/modules/database/assets/images/icons/' . $item['icon'] ?>.png');"></ins><del></del></span><?= $item['icon'] ?></a></div>
                                            </li>
                                            <?php if ($item['BuyPrice']) : ?>
                                                <li>
                                                    <div> Buy for: <?= formatSellPrice($item['BuyPrice']) ?></div>
                                                </li>
                                            <?php endif;
                                            if ($item['SellPrice']) : ?>
                                                <li>
                                                    <div> Sells for: <?= formatSellPrice($item['SellPrice']) ?></div>
                                                </li>
                                            <?php endif;
                                            if ($item['partyloot']) : ?>
                                                <li>
                                                    <div><span class="static-tooltip" data-tooltip="When this item drops,&#013;&#010; each member of the group can loot one.">Party Loot</span></div>
                                                </li>
                                            <?php endif;
                                          
                                            if ($item['DisenchantID'] && $item['class'] != 0) : ?>
                                                <li>
                                                    <div>Disenchantable</div>
                                                </li>
                                            <?php else : ?>
                                                <li>
                                                    <div>Cannot be disenchanted</div>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php if ($item['creature_list'] || $item['item_list'] || $item['go_list'] || $item['fished_list'] || $item['reward_list'] || $item['vendor_list']) : ?>
                <div class="text">
                    <h2 class="clear">Related</h2>
                </div>
                <ul class="uk-tab" data-uk-tab="{connect:'#tab-id'}">
                    <?php if ($item['creature_list']) :
                        if (array_key_exists('drop', $item['creature_list'])): ?>
                            <li><a href="">Dropped By (<?= count($item['creature_list']['drop']) ?>)</a></li>
                        <?php endif;
                        if (array_key_exists('pocket', $item['creature_list'])): ?>
                            <li><a href="">Pickpocketed From (<?= count($item['creature_list']['pocket']) ?>)</a></li>
                        <?php endif;
                        if (array_key_exists('skin', $item['creature_list'])): ?>
                            <li><a href="">Skinned From (<?= count($item['creature_list']['skin']) ?>)</a></li>
                        <?php endif;
                    endif;
                    if ($item['item_list']) :
                        if (array_key_exists('contained', $item['item_list'])): ?>
                            <li><a href="">Contained In <small>(Item)</small> (<?= count($item['item_list']['contained']) ?>)</a></li>
                        <?php endif;
                        if (array_key_exists('disenchanted', $item['item_list'])): ?>
                            <li><a href="">Disenchanted From (<?= count($item['item_list']['disenchanted']) ?>)</a></li>
                        <?php endif;
                        if (array_key_exists('contains', $item['item_list'])): ?>
                            <li><a href="">Contains (<?= count($item['item_list']['contains']) ?>)</a></li>
                        <?php endif;
                        if (array_key_exists('disenchanting', $item['item_list'])): ?>
                            <li><a href="">Disenchants (<?= count($item['item_list']['disenchanting']) ?>)</a></li>
                        <?php endif;
                    endif;
                    if ($item['go_list']) :
                        if (array_key_exists('mined', $item['go_list'])): ?>
                            <li><a href="">Mined From (<?= count($item['go_list']['mined']) ?>)</a></li>
                        <?php endif;
                        if (array_key_exists('gathered', $item['go_list'])): ?>
                            <li><a href="">Gathered From (<?= count($item['go_list']['gathered']) ?>)</a></li>
                        <?php endif;
                        if (array_key_exists('contained', $item['go_list'])): ?>
                            <li><a href="">Contained In <small>(Object)</small> (<?= count($item['go_list']['contained']) ?>)</a></li>
                        <?php endif;
                    endif;
                    if ($item['fished_list']) : ?>
                        <li><a href="">Fished In (<?= count($item['fished_list']) ?>)</a></li>
                    <?php endif;
                    if ($item['start_q_info']) : ?>
                        <li><a href="">Starts (1)</a></li>
                    <?php endif;
                    if ($item['reward_list']) : ?>
                        <li><a href="">Reward From (<?= count($item['reward_list']) ?>)</a></li>
                    <?php endif;
                    if ($item['vendor_list']) : ?>
                        <li><a href="">Sold By (<?= count($item['vendor_list']) ?>)</a></li>
                    <?php endif; ?>
                </ul>
                <ul id="tab-id" class="uk-switcher uk-margin">
                    <?php if ($item['creature_list']) :
                        foreach ($item['creature_list'] as $crt) :
                            if ($crt):?>
                                
                                <li>
                                    <div class="uk-overflow-auto uk-margin-small">
                                        <table class="uk-table uk-table-small uk-table-divider uk-table-hover uk-table-middle yesilcms-table">
                                            <thead>
                                            <tr>
                                                <th class="uk-preserve-width">Name</th>
                                                <th class="uk-text-center">Level</th>
                                                <th>Location</th>
                                                <th class="uk-text-center">Type</th>
                                                <th class="uk-text-center">Drop Chance</th>
                                                <th class="uk-text-center">Condition</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($crt as $drop) : ?>
                                                <tr>
                                                    <td><span class="q"><?= $drop['Name'] ?></span></td>
                                                    <td class="uk-text-center"><?= ($drop['MaxLevel'] >= 63 ? '??' : ($drop['MinLevel'] == $drop['MaxLevel'] ? $drop['MaxLevel'] : $drop['MinLevel'] . ' - ' . $drop['MaxLevel'])) ?></td>
                                                    <!-- A Crash here in getZoneName (24 aug 24) --><td><?= $this->Database_model->getZoneName($drop['entry'] ?? -1, $drop['    '] ?? -1,$realmid) ?></td>
                                                    <td class="uk-text-center"><?= creatureRank($drop['Rank']) ?></td>
                                                    <td class="uk-text-center"><?= formatPercentage($drop['percent']) ?></td>
                                                    <td class="uk-text-center"> <?php if ($drop['condition_id'] > 0) : ?>
<span class="q2 uk-text-bold static-tooltip" data-tooltip="<?= $this->Database_model->describeCondition($this->Database_model->findConditionByID($drop['condition_id'],$realmid), false,$realmid) ?>"><?= $drop['condition_id'] ?></span>
                                                        <?php else: ?>
                                                            <span class="q0">n/a</span>
                                                        <?php endif; ?>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </li>
                            <?php endif;
                        endforeach;
                    endif;
                    if ($item['item_list']) :
                        foreach ($item['item_list'] as $ilt) :
                            if ($ilt):?>
                                <li>
                                    <div class="uk-overflow-auto uk-margin-small">
                                        <table class="uk-table uk-table-small uk-table-divider uk-table-hover uk-table-middle yesilcms-table">
                                            <thead>
                                            <tr>
                                                <th class="uk-table-shrink">Name</th>
                                                <th class="uk-text-center">Level</th>
                                                <th class="uk-text-center">Req Level</th>
                                                <th class="uk-text-center">Type</th>
                                                <th class="uk-text-center">Drop Chance</th>
                                                <th class="uk-text-center">Condition</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($ilt as $it) : ?>
                                                <tr>
                                                    <td style="min-width: 250px;">
                                                    <span class="iconmedium">
                                                        <ins class="yesilcms-lazy" style="background-image: url('<?= base_url() . 'application/modules/database/assets/images/icons/' . $this->Database_model->getIconName($it['entry'], $realmid) ?>.png');"></ins>
                                                        <del></del>
                                                        <?php if ($it['maxcount'] > 1) : ?>
                                                            <span class="stackable stackable-sm"><?= $it['mincount'] ?><?= $it['maxcount'] > 1 ? ' - ' . $it['maxcount'] : '' ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                        <a href="<?= base_url($lang) ?>/item/<?= $it['entry'] ?>/<?= $realmid ?>" data-item="item=<?= $it['entry'] ?>" data-realm="<?= $realmid ?>" data-patch='<?= dataPatch($patch) ?>'><span class="q<?= $it['Quality'] ?>"><?= $it['name'] ?></span></a>
                                                    </td>
                                                    <td class="uk-text-center"><?= $it['ItemLevel'] ?></td>
                                                    <td class="uk-text-center"><?= (int)$it['RequiredLevel'] !== 0 ? $it['RequiredLevel'] : '<span class="q0">n/a</span>' ?></td>
                                                    <td class="uk-text-center"><?= itemSubClass($it['class'], $it['subclass']) ?></td>
                                                    <td class="uk-text-center"><?= formatPercentage($it['percent']) ?></td>
                                                    <td class="uk-text-center"> <?php if ($it['condition_id'] > 0) : ?>
                                                            <span class="q2 uk-text-bold static-tooltip" data-tooltip="<?= $this->Database_model->describeCondition($this->Database_model->findConditionByID($it['condition_id'],$realmid), false,$realmid) ?>"><?= $it['condition_id'] ?></span>
                                                        <?php else: ?>
                                                            <span class="q0">n/a</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </li>
                            <?php endif;
                        endforeach;
                    endif;
                    if ($item['go_list']) :
                        foreach ($item['go_list'] as $gobj) :
                            if ($gobj): ?>
                                <li>
                                    <div class="uk-overflow-auto uk-margin-small">
                                        <table class="uk-table uk-table-small uk-table-divider uk-table-hover uk-table-middle yesilcms-table">
                                            <thead>
                                            <tr>
                                                <th class="uk-preserve-width">Name</th>
                                                <th class="uk-text-center">Location</th>
                                                <th class="uk-text-center">Type</th>
                                                <th class="uk-text-center">Drop Chance</th>
                                                <th class="uk-text-center">Condition</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($gobj as $obj) : ?>
                                                <tr>
                                                    <td><span class="q"><?= $obj['name'] ?></span></td>
                                                    <td class="uk-text-center"><?= $this->Database_model->getZoneName($obj['entry'], $obj['map'] ?? -1, $realmid,'gameobject') ?></td>
                                                    <td class="uk-text-center"><?= GOTypeByID($obj['type']) ?></td>
                                                    <td class="uk-text-center"><?= formatPercentage($obj['percent']) ?></td>
                                                    <td class="uk-text-center"> <?php if ($obj['condition_id'] > 0) : ?>
                                                            <span class="q2 uk-text-bold static-tooltip" data-tooltip="<?= $this->Database_model->describeCondition($this->Database_model->findConditionByID($obj['condition_id'],$realmid), false,$realmid) ?>"><?= $obj['condition_id'] ?></span>
                                                        <?php else: ?>
                                                            <span class="q0">n/a</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </li>
                            <?php endif;
                        endforeach;
                    endif;
                    if ($item['fished_list']) : ?>
                        <li>
                            <div class="uk-overflow-auto uk-margin-small">
                                <table class="uk-table uk-table-small uk-table-divider uk-table-hover uk-table-middle yesilcms-table">
                                    <thead>
                                    <tr>
                                        <th class="uk-preserve-width">Name</th>
                                        <th class="uk-text-center">Level</th>
                                        <th class="uk-text-center">Territory</th>
                                        <th class="uk-text-center">Category</th>
                                        <th class="uk-text-center">Drop Chance</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($item['fished_list'] as $zone) : ?>
                                        <tr>
                                            <td><span class="q"><?= $zone['name'] ?></span></td>
                                            <td class="uk-text-center"><?= (int)$zone['area_level'] !== 0 ? $zone['area_level'] : '<span class="q0">n/a</span>' ?></td>
                                            <td class="uk-text-center"><?= territoryByTeamID($zone['team']) ?></td>
                                            <td class="uk-text-center"><?= zoneCatByMapID($zone['map_id']) ?></td>
                                            <td class="uk-text-center"><?= formatPercentage($zone['percent'])?> %</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </li>
                    <?php endif;
                    if ($item['start_q_info']) : ?>
                        <li>
                            <div class="uk-overflow-auto uk-margin-small">
                                <table class="uk-table uk-table-small uk-table-divider uk-table-hover uk-table-middle yesilcms-table">
                                    <thead>
                                    <tr>
                                        <th class="uk-preserve-width">Name</th>
                                        <th class="uk-text-center">Level</th>
                                        <th class="uk-text-center">Req</th>
                                        <th class="uk-text-center">Side</th>
                                        <?php if ($item['start_q_info']['itemrewards']) : ?>
                                            <th class="uk-text-center">Rewards</th>
                                        <?php endif; ?>
                                        <th class="uk-text-center">Experience</th>
                                        <th class="uk-text-center">Money</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><?= $item['start_q_info']['Title'] ?></td>
                                        <td class="uk-text-center"><?= $item['start_q_info']['QuestLevel'] ?></td>
                                        <td class="uk-text-center"><?= $item['start_q_info']['MinLevel'] ?></td>
                                        <td class="uk-text-center"><?= sideByID(sideByRaceMask($item['start_q_info']['RequiredRaces'])) ?></td>
                                        <?php if ($item['start_q_info']['itemrewards']) : ?>
                                            <td class="uk-text-center">
                                                <?php foreach ($item['start_q_info']['itemrewards'] as $rew) : ?>
                                                    <div style="margin: 0 auto; text-align: left; width: 52px;">
                                                        <div class="iconsmall" style="float: left;">
                                                            <ins class="yesilcms-lazy" style="background-image: url('<?= base_url() . 'application/modules/database/assets/images/icons/' . $rew['icon'] ?>.png')"></ins>
                                                            <del></del>
                                                            <a href="<?= base_url($lang) ?>/item/<?= $rew['entry'] ?>/<?= $realmid ?>" data-item="item=<?= $rew['entry'] ?>" data-realm='<?= $realmid ?>'></a>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </td>
                                        <?php endif; ?>
                                        <td class="uk-text-center"><?= $item['start_q_info']['RewXP'] ?></td>
                                        <td class="uk-text-center"><?= $item['start_q_info']['RewOrReqMoney'] > 0 ? formatSellPrice($item['start_q_info']['RewOrReqMoney']) : '<span class="q0">n/a</span>' ?></td>

                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </li>
                    <?php endif;
                    if ($item['reward_list']) : ?>
                        <li>
                            <div class="uk-overflow-auto uk-margin-small">
                                <table class="uk-table uk-table-small uk-table-divider uk-table-hover uk-table-middle yesilcms-table">
                                    <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th class="uk-text-center">Level</th>
                                        <th class="uk-text-center">Req. Level</th>
                                        <th class="uk-text-center">Rewards</th>
                                        <th class="uk-text-center">Experience</th>
                                        <th class="uk-text-center">Money</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($item['reward_list'] as $reward) : ?>
                                        <tr>
                                            <td><span class="q"><?= $reward['Title'] ?></span></td>
                                            <td class="uk-text-center"><?= $reward['QuestLevel'] ?></td>
                                            <td class="uk-text-center"><?= $reward['MinLevel'] ?></td>
                                            <td class="uk-text-center"><span class="q<?= $reward['quality'] ?>"><a href="<?= base_url($lang) ?>/item/<?= $reward['entry'] ?>/<?= $realmid ?>"><?= $reward['name'] ?></a></span></td>
                                            <td class="uk-text-center"><?= $reward['RewMoneyMaxLevel'] ?></td>
                                            <td class="uk-text-center"><?= $reward['RewOrReqMoney'] > 0 ? formatSellPrice($reward['RewOrReqMoney']) : '<span class="q0">n/a</span>' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </li>
                    <?php endif;
                    if ($item['vendor_list']) : ?>
                        <li>
                            <div class="uk-overflow-auto uk-margin-small">
                                <table class="uk-table uk-table-small uk-table-divider uk-table-hover uk-table-middle yesilcms-table">
                                    <thead>
                                    <tr>
                                        <th class="uk-preserve-width">Name</th>
                                        <th class="uk-text-center">Level</th>
                                        <th>Location</th>
                                        <th class="uk-text-center">Stock</th>
                                        <th class="uk-text-center">Cost</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($item['vendor_list'] as $vendor) : ?>
                                        <tr>
                                            <td><span class="q"><?= $vendor['Name'] ?></span>
                                                <div class="small">&lt;<?= $vendor['SubName'] ?>&gt;</div>
                                            </td>
                                            <td class="uk-text-center"><?= $vendor['MinLevel'] ?></td>
                                            <td><?= $this->Database_model->getZoneName($vendor['Entry'], $vendor['map'] ?? -1,$realmid) ?></td>
                                            <td class="uk-text-center"><?= (int)$vendor['maxcount'] === 0 ? '∞' : $vendor['maxcount'] ?></td>
                                            <td class="uk-text-center"><?= $vendor['BuyPrice'] > 0 ? formatSellPrice($vendor['BuyPrice']) : '<span class="q0">n/a</span>' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        <?php else : ?>
            <div class="uk-alert-danger uk-text-center uk-position-center" style="width:75%" uk-alert>
                <p>We searched all over Azeroth, but unfortunately we couldn't find the item you are looking for..:(</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<div id="tooltip" class="tooltip yesilcms-tooltip"></div>
<script type="text/javascript" src="<?= base_url() . 'application/modules/database/assets/js/tooltip.js'; ?>"></script>
<script type="text/javascript" src="<?= base_url() . 'application/modules/database/assets/js/jquery.dataTables.min.js'; ?>"></script>
<script type="text/javascript" src="<?= base_url() . 'application/modules/database/assets/js/dataTables.uikit.min.js'; ?>"></script>
<script type="text/javascript" src="/jquery.lazy.min.js"></script> <!-- A. Blohme: For some reason the original path gave us access-denied so I had to move it for now under development! -->

<script>
    const baseURL = "<?= base_url($lang); ?>";
    const imgURL = "<?= base_url() . 'application/modules/database/assets/images/icons/'; ?>";
    $(document).ready(function () {
        $('table.uk-table').DataTable({
            order: [[4, 'desc']],
            "columnDefs": [{
                "targets": 'no-sort',
                "orderable": false,
            }]
        });
    });
    $(function () {
        $('.yesilcms-lazy').lazy({
            combined: true,
            delay: 30000
        });
    });
</script>
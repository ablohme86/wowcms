<?php

defined('BASEPATH') or exit('No direct script access allowed');


/**
 * @property General_model       $wowgeneral
 * @property CI_DB_query_builder $world
 */
class Database_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->world = $this->load->database('world', true);
		$this->world_tbc = $this->load->database('world_tbc', true);
        
    }

    /**
     * @param  int  $id
     * @param  int  $patch
     *
     * @return array
     */
    public function getItem(int $id, int $realmid): array // TODO: No need to cache this, consider removing cache here
    {
       // $itemCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('itemID_' . $id . '-P_' . $patch) : false;


		$isExp = $this->wowrealm->isTbc($realmid);

		
		switch ($isExp) 
		{
			case 0:
				$subQ = $this->world->select( false)->where("t1.entry=t2.entry")->get_compiled_select("item_template t2");
				$item = $this->world->select()->where('entry', $id)->limit(1)->get('item_template t1')->row_array();
			break;


			case 1:
				$subQ = $this->world_tbc->select( false)->where("t1.entry=t2.entry")->get_compiled_select("item_template t2");
				$item = $this->world_tbc->select()->where('entry', $id)->limit(1)->get('item_template t1')->row_array();
			break;
		}


        return $item ?? [];
    }

    /**
     * @param  int  $id
     * @param  int  $patch
     *
     * @return string
     */
    public function getItemName(int $id, $realmid,int $patch = 10): string
    {
        
        $itemNameCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('itemNameID_' . $id . '-P_10') : false;
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        
        
        if ($itemNameCache) {
            $item_name = $itemNameCache;
        } else {
            $subQ      = $world_db->select("*", false)->where("t1.entry=t2.entry")->get_compiled_select("item_template t2");
            $item_name = $world_db->select('name')->where('entry', $id)->limit(1)->get('item_template t1')->row('name');

            if ($item_name) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('itemNameID_' . $id . '-P_10', $item_name, 60 * 60 * 24 * 30);
                }
            }
        }
		
        return $item_name ?? 'Unknown';
    }

    public function getCreature(int $id, $realmid, string $column = ''): array
    {
   
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $creatureCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('creatureID_' . $id . '-P_10' . ($column ? '-Col_' . str_replace(", ", "-", $column) : '')) : false;

        if ($creatureCache) {
            $creature = $creatureCache;
        } else {
            $subQ     = $world_db->select("*", false)->where("t1.entry=t2.entry")->get_compiled_select("creature_template t2");
            $creature = $world_db->select($column)->where('entry', $id)->limit(1)->get('creature_template t1')->row_array();

            if ($creature) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('creatureID_' . $id . '-P_10' . ($column ? '-Col_' . str_replace(", ", "-", $column) : ''), $creature, 60 * 60 * 24 * 30);
                }
            }
        }

        return $creature ?? [];
    }

    public function getCreatureName(int $id, $realmid,int $patch = 10): string
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $creatureNameCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('creatureNameID_' . $id . '-P_10') : false;

        if ($creatureNameCache) {
            $creature_name = $creatureNameCache;
        } else {
            $subQ          = $world_db->select("", false)->where("t1.entry=t2.entry")->get_compiled_select("creature_template t2");
            $creature_name = $world_db->select('name')->where('entry', $id)->limit(1)->get('creature_template t1')->row('name');

            if ($creature_name) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('creatureNameID_' . $id . '-P_' . $patch, $creature_name, 60 * 60 * 24 * 30);
                }
            }
        }

        return $creature_name ?? ('Unknown - ' . $id);
    }

    /**
     * @param  int     $id
     * @param  int     $patch
     * @param  string  $column
     *
     * @return array
     */
    public function getSpell(int $id, $realmid,int $patch = 10, string $column = ''): array
    {
        
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        
        $spellCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('spellID_' . $id . '-P_10' . ($column ? '-Col_' . str_replace(", ", "-", $column) : '')) : false;

        if ($spellCache) 
        {
            $spell = $spellCache;
        } 
        else 
        {
            $subQ  = $world_db->select("*", false)->where("t1.entry=t2.Id")->get_compiled_select("spell_template t2");
            $spell = $world_db->select($column)->where('Id', $id)->limit(1)->get('spell_template t1')->row_array();
            
            if ($spell) 
            {
                if ($this->wowgeneral->getRedisCMS()) 
                {
                    // Cache for 30 day
                    $this->cache->redis->save('spellID_' . $id . '-P_10'  . ($column ? '-Col_' . str_replace(", ", "-", $column) : ''), $spell, 60 * 60 * 24 * 30);
                }
            }
        }

        return $spell ?? [];
    }

    /**
     * @param  int  $id
     * @param  int  $patch
     *
     * @return array
     */

	 // A.BLOMHE 22.08.24: FIX ALL WORLD AND'ENTRY' BS
    public function getAffectedSpellList(int $id,$realmid, int $patch = 10): array
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $affectedSpellsCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('affectedSpellList_' . $id . '-P_10') : false;

        if ($affectedSpellsCache) {
            $affectedSpells = $affectedSpellsCache;
        } else {
            $subQ           = $world_db->select("*", false)->where("t1.entry=t3.Id")->get_compiled_select("spell_template t3");
            $subQ2          = $world_db->select("*", false)->where("t2.Id=t4.Id")->get_compiled_select("spell_template t4");
         //   $affectedSpells = $world_db->select('t2.Id,  t2.SpellIconID, t2.SpellName, t2.nameSubtext')->where("t1.Id", $id)->where('(t1.SpellFamilyName = t2.SpellFamilyName)')->where('(t1.EffectItemType1 & t2.SpellFamilyFlags)')->order_by('t2.SpellName, LENGTH(t2.nameSubText), t2.nameSubtext', 'ASC')->order_by('nameSubtext', 'ASC')->get('spell_template t1, spell_template t2')->result_array();
   $affectedSpells = $world_db->select('t2.Id,  t2.SpellIconID, t2.SpellName')->where("t1.Id", $id)->where('(t1.SpellFamilyName = t2.SpellFamilyName)')->where('(t1.EffectItemType1 & t2.SpellFamilyFlags)')->order_by('t2.SpellName', 'ASC')->get('spell_template t1, spell_template t2')->result_array();
   
   
            if ($affectedSpells) 
			{
                if ($this->wowgeneral->getRedisCMS()) 
				{
                    // Cache for 30 day
                    $this->cache->redis->save('affectedSpellList_' . $id . '-P_10', $affectedSpells, 60 * 60 * 24 * 30);
                }
            }
        }

        return $affectedSpells ?? [];
    }

    /**
     * @param  int  $id
     * @param  int  $patch
     *
     * @return string
     */


	// A. Blohme 
	// 13:09 30.08.2024
	// Added some character info retrieval functions

	/*public function getCharacterData($guid, $realmid)
	{
		
	
	}*/

	public function searchForCharacter($search_string, $realmid)
	{
       $realmConnection = $this->wowrealm->getRealmConnectionData($realmid);
       $search_string = "" . $search_string . "";
        
        // Utføre søket
        $search = $realmConnection->select("name,guid,level,class,race,money")->like("name", $search_string)->get("characters");
        
        if ($search->num_rows() < 1) {
            return false;
        }
        return $search->result();
	}

   public function getCharacterData($guid,$realmid)
   {
       $realmConnection = $this->wowrealm->getRealmConnectionData($realmid);
        // Utføre søket
        $search = $realmConnection->select("*")->where("guid", $guid)->get("characters");
        
        if ($search->num_rows() < 1) {
            return false;
        }
        return $search->result();
   }


    public function getReqSpellName(int $id,int $realmid, int $patch = 10): string
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $spellNameCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('spellNameID_' . $id . '-P_10') : false;

        if ($spellNameCache) {
            $spell = $spellNameCache;
        } else {
            $subQ  = $world_db->select("*", false)->where("t1.entry=t2.entry")->get_compiled_select("spell_template t2");
            $spell = $world_db->select()->where('entry', $id)->where('build=(' . $subQ . ')')->limit(1)->get('spell_template t1')->row('name');

            if ($spell) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('spellNameID_' . $id . '-P_10', $spell, 60 * 60 * 24 * 30);
                }
            }
        }

        return $spell ?? 'Unknown';
    }

    /**
     * @param  int     $id
     * @param  int     $patch
     * @param  string  $field
     *
     * @return string
     */
    public function getSpellField(int $id, $realmid,int $patch = 10, string $field = ''): string //This is barely used, it fetches values from other spells for tooltip
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        
        $spellFieldCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('spellFieldID_' . $id . '-P_10' . ($field ? '-Fld_' . $field : '')) : false;
        if ($spellFieldCache) {
            $spell = $spellFieldCache;
        } else {
            $subQ  = $world_db->select("", false)->where("t1.entry=t2.entry")->get_compiled_select("spell_template t2");
            $spell = $world_db->select($field)->where('entry', $id)->where('build=(' . $subQ . ')')->limit(1)->get('spell_template t1')->row($field);

            if ($spell) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('spellFieldID_' . $id . '-P_10'  . ($field ? '-Fld_' . $field : ''), $spell, 60 * 60 * 24 * 30);
                }
            }
        }

        return $spell ?? '';
    }

    /**
     * @param  int  $id
     * @param  int  $patch
     *
     * @return string
     */
    public function getSpellDetails(int $id,$realmid, int $patch = 10): string //No need to cache
    {
        $signs = ['+', '-', '/', '*', '%', '^'];

        $spell = $this->getSpell($id,$realmid, "10");
        $final = [];

        if (empty($spell)) {
            return 'Unknown';
        }
        $desc = nl2br($spell['description']);

        preg_match_all('/\$(?(?=[\*\/])([\/\*]+\d+;\d*\w*)|(\w*\d*))/i', $spell['description'], $matches);
        $matches[1] = array_replace($matches[1], array_filter($matches[2]));
        unset($matches[2]);

        // Split numbers after desired character into separate group on purpose.
        foreach ($matches[1] as $key => $fix) {
            if (preg_match('/^\b(\d+)([adnoqrstu])(\d*)?/', $fix, $match)) {
                $matches[1][$key] = $match[2] . ($match[3] ? $match[3] . ';' : ';') . $match[1];
            }
            if (preg_match('/^([\*\/])(\d+)[;](\d*)([fmos])(\d*)?/i', $fix, $matcha)) {
                $matches[1][$key] = $matcha[4] . ($matcha[5] ? $matcha[5] . '{' : '{') . $matcha[1] . '}[' . $matcha[2] . '];' . $matcha[3];
            }
        }

        foreach ($matches[0] as $key => $var) {
            if ((strtolower($matches[1][$key][0]) ?? '') === 'a') {
                $index = isInt($matches[1][$key][1] ?? '') ? $matches[1][$key][1] : getIndex($var);

                if ($index <= 3) {
                    $entry = (($pos = strpos($matches[1][$key], ";")) > 0) ? substr($matches[1][$key], $pos + 1) : '';

                    $res = isInt($entry)
                        ? $this->getSpellField($entry,$realmid, $patch, 'EffectRadiusIndex' . $index)
                        : $spell['EffectRadiusIndex' . $index];

                    $final[$var] = $res;
                }
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'b') {
                $index = getIndex($var);
                if ($index <= 3) {
                    $final[$var] = $spell['EffectPointsPerComboPoint' . $index];
                }
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'd') {
                $entry = (($pos = strpos($matches[1][$key], ";")) > 0) ? substr($matches[1][$key], $pos + 1) : '';

                $res = isInt($entry)
                    ? $this->config->item('duration')[$this->getSpellField($entry,$realmid, $patch, 'DurationIndex')]
                    : $this->config->item('duration')[$spell['DurationIndex']];

                if ($res < 0) {
                    $final[$var] = 'until cancelled';
                } else {
                    $final[$var] = formatTime($res, true);
                }
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'e') {
                $index = getIndex($var);
                $index = $index == 0 ? 1 : $index;
                if ($index <= 3) {
                    $final[$var] = $spell['EffectMultipleValue' . $index];
                }
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'f') {
                $index = isInt($matches[1][$key][1] ?? '') ? $matches[1][$key][1] : getIndex($var);
                $index = $index == 0 || $index > 3 ? 1 : $index; //just to be safe…
                if ($index <= 3) {
                    $op = getBetweenStr($matches[1][$key], '{', '}');
                    if ($op !== '') {
                        $act = (int)getBetweenStr($matches[1][$key], '[', ']') ?? 1;
                    }

                    $entry = (($pos = strpos($matches[1][$key], ";")) > 0) ? substr($matches[1][$key], $pos + 1) : '';

                    $res = isInt($entry)
                        ? $this->getSpellField($entry,$realmid, $patch, 'dmgMultiplier' . $index)
                        : $spell['dmgMultiplier' . $index];

                    if (in_array($op, $signs)) {
                        eval("\$res = $res $op $act;");
                    }

                    $final[$var] = $res;
                }
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'g') {
                $final[$var] = 'herself/himself';
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'h') {
                $final[$var] = $spell['procChance'];
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'l') { //lazymode on
                $singular    = substr($var, 2);
                $final[$var] = $singular;
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'm') {
                $index = isInt($matches[1][$key][1] ?? '') ? $matches[1][$key][1] : getIndex($var);
                $index = $index == 0 || $index > 3 ? 1 : $index; //just to be safe…

                if ($index <= 3) {
                    $op = getBetweenStr($matches[1][$key], '{', '}');
                    if ($op !== '') {
                        $act = (int)getBetweenStr($matches[1][$key], '[', ']') ?? 1;
                    }

                    $entry = (($pos = strpos($matches[1][$key], ";")) > 0) ? substr($matches[1][$key], $pos + 1) : '';

                    $res = isInt($entry)
                        ? $this->getSpell($entry, $realmid,$patch, 'EffectBasePoints' . $index . ', EffectDieSides' . $index)
                        : $spell['EffectBasePoints' . $index];

                    $add = ($matches[1][$key][0]) === 'm' ? 1 : $res['EffectDieSides' . $index] ?? $spell['EffectDieSides' . $index];
                    $res = $res + $add;

                    if (in_array($op, $signs)) {
                        eval("\$res = $res $op $act;");
                    }

                    $final[$var] = round($res, 2);
                }
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'n') {
                $entry = (($pos = strpos($matches[1][$key], ";")) > 0) ? substr($matches[1][$key], $pos + 1) : '';

                $res = isInt($entry)
                    ? $this->getSpellField($entry,$realmid, $patch, 'procCharges')
                    : $spell['procCharges'];

                $final[$var] = $res;
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'o') {
                $index = isInt($matches[1][$key][1] ?? '') ? $matches[1][$key][1] : getIndex($var);
                $index = $index == 0 || $index > 3 ? 1 : $index; //just to be safe…

                $op = getBetweenStr($matches[1][$key], '{', '}');
                if ($op !== '') {
                    $act = (int)getBetweenStr($matches[1][$key], '[', ']') ?? 1;
                }
                $entry = (($pos = strpos($matches[1][$key], ";")) > 0) ? substr($matches[1][$key], $pos + 1) : '';

                if (isInt($entry)) {
                    $n_spell = $this->getSpell($entry,$realmid, $patch, 'DurationIndex, EffectDieSides' . $index . ', EffectBasePoints' . $index . ', EffectApplyAuraName' . $index . ',EffectAmplitude' . $index);

                    $period   = $n_spell['EffectAmplitude' . $index];
                    $duration = $this->config->item('duration')[$n_spell['DurationIndex']];
                    $base     = $n_spell['EffectBasePoints' . $index];
                    $add      = $n_spell['EffectDieSides' . $index];
                    $aura     = $n_spell['EffectApplyAuraName' . $index];
                } else {
                    $period   = $spell['EffectAmplitude' . $index];
                    $duration = $this->config->item('duration')[$spell['EurationIndex']];
                    $base     = $spell['EffectBasePoints' . $index];
                    $add      = $spell['EffectDieSides' . $index];
                    $aura     = $spell['EffectApplyAuraName' . $index];
                }

                $min = $add ? $base + 1 : $base;
                $max = $base + $add;

                if (! $period) {
                    if ($aura == 84 || $aura == 85) {
                        $period = 5000;
                    } else {
                        $period = 3000;
                    }
                }

                $min *= $duration / $period;
                $max *= $duration / $period;

                if (in_array($op, $signs)) {
                    eval("\$min = $min $op $act;");
                    eval("\$max = $max $op $act;");
                }

                if ($min === $max) {
                    $final[$var] = $max;
                } else {
                    $final[$var] = $min . ' to ' . $max;
                }
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'q') {
                $index = isInt($matches[1][$key][1] ?? '') ? $matches[1][$key][1] : getIndex($var);
                $index = $index == 0 || $index > 3 ? 1 : $index; //just to be safe…

                if ($index <= 3) {
                    $entry = (($pos = strpos($matches[1][$key], ";")) > 0) ? substr($matches[1][$key], $pos + 1) : '';

                    $res = isInt($entry)
                        ? $this->getSpellField($entry, $realmid,$patch, 'EffectMiscValue' . $index)
                        : $spell['EffectMiscValue' . $index];

                    $final[$var] = $res;
                }
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'r') {
                $index = isInt($matches[1][$key][1] ?? '') ? $matches[1][$key][1] : getIndex($var);
                $index = $index == 0 || $index > 3 ? 1 : $index; //just to be safe…
                if ($index <= 3) {
                    $entry = (($pos = strpos($matches[1][$key], ";")) > 0) ? substr($matches[1][$key], $pos + 1) : '';

                    $res = isInt($entry)
                        ? $this->getSpellField($entry, $realmid,$patch, 'EffectRadiusIndex' . $index)
                        : $spell['EffectRadiusIndex' . $index];

                    $final[$var] = $res;
                }
                //don't need the first condition, just to make double sure
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 's') {
                $index = isInt($matches[1][$key][1] ?? '') ? $matches[1][$key][1] : getIndex($var);
                $index = $index == 0 || $index > 3 ? 1 : $index; //just to be safe…
                if ($index <= 3) {
                    $op = getBetweenStr($matches[1][$key], '{', '}');
                    if ($op !== '') {
                        $act = (int)getBetweenStr($matches[1][$key], '[', ']') ?? 1;
                    }
                    $entry = (($pos = strpos($matches[1][$key], ";")) > 0) ? substr($matches[1][$key], $pos + 1) : '';
                    $base = "hey";
                    
                    if (isInt($entry)) {
                        $n_spell = $this->getSpell($entry, $realmid,$patch, 'EffectDieSides' . $index . ', EffectBasePoints' . $index);

                        $base = $n_spell['EffectBasePoints' . $index];
                        $add  = $n_spell['EffectDieSides' . $index];
                    } else {
                        $base = $spell['EffectBasePoints' . $index];
                        $add  = $spell['EffectDieSides' . $index];
                    }

                    $min = $add ? $base + 1 : $base;
                    $max = $base + $add;

                    if (in_array($op, $signs)) {
                        eval("\$min = $min $op $act;");
                        eval("\$max = $max $op $act;");
                    }

                    if ($min === $max) {
                        $final[$var] = $max;
                    } else {
                        $final[$var] = $min . ' to ' . $max;
                    }
                }
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 't') {
                $index = isInt($matches[1][$key][1] ?? '') ? $matches[1][$key][1] : getIndex($var);
                $index = $index == 0 || $index > 3 ? 1 : $index; //just to be safe…
                if ($index <= 3) {
                    $entry = (($pos = strpos($matches[1][$key], ";")) > 0) ? substr($matches[1][$key], $pos + 1) : '';

                    $res = isInt($entry)
                        ? $this->getSpellField($entry,$realmid, $patch, 'EffectAmplitude' . $index)
                        : $spell['EffectAmplitude' . $index];

                    $final[$var] = $res / 1000;
                }
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'u') {
                $index = isInt($matches[1][$key][1] ?? '') ? $matches[1][$key][1] : getIndex($var);
                $index = $index == 0 || $index > 3 ? 1 : $index; //just to be safe…
                if ($index <= 3) {
                    $entry = (($pos = strpos($matches[1][$key], ";")) > 0) ? substr($matches[1][$key], $pos + 1) : '';

                    $res = isInt($entry)
                        ? $this->getSpellField($entry,$realmid, $patch, 'stackAmount')
                        : $spell['stackAmount'];

                    $final[$var] = $res;
                }
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'v') {
                $final[$var] = $spell['maxTargetLevel'];
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'x') {
                $index = getIndex($var);
                if ($index <= 3) {
                    $final[$var] = $spell['EffectChainTarget' . $index];
                }
            } elseif ((strtolower($matches[1][$key][0]) ?? '') === 'z') {
                $final[$var] = htmlspecialchars('<Inn>');
            }
        }

        foreach ($final as $key => $value) {
            $desc = str_replace($key, str_replace(['-', '+'], '', $value), $desc);
        }

        return $desc;
    }

    public function getGO(int $id,$realmid, int $patch = 10, string $column = ''): array
    {
        $goCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('gameObjectID_' . $id . '-P_10'  . ($column ? '-Col_' . str_replace(", ", "-", $column) : '')) : false;
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        if ($goCache) {
            $go = $goCache;
        } else {
            $subQ = $world_db->select("", false)->where("t1.entry=t2.entry")->get_compiled_select("gameobject_template t2");
            $go   = $world_db->select($column)->where('entry', $id)->limit(1)->get('gameobject_template t1')->row_array();

            if ($go) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('gameObjectID_' . $id . '-P_10'  . ($column ? '-Col_' . str_replace(", ", "-", $column) : ''), $go, 60 * 60 * 24 * 30);
                }
            }
        }

        return $go ?? [];
    }

    public function getGOName(int $id, $realmid,int $patch = 10): string
    {
        $goNameCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('gameObjectNameID_' . $id . '-P_' . $patch) : false;
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        if ($goNameCache) {
            $go_name = $goNameCache;
        } else {
            $subQ    = $world_db->select("*", false)->where("t1.entry=t2.entry")->get_compiled_select("gameobject_template t2");
            $go_name = $world_db->select('name')->where('entry', $id)->limit(1)->get('gameobject_template t1')->row('name');

            if ($go_name) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('gameObjectNameID_' . $id . '-P_10', $go_name, 60 * 60 * 24 * 30);
                }
            }
        }

        return $go_name ?? ('Unknown - ' . $id);
    }

    public function getQuest(int $id,$realmid, int $patch = 10, string $column = ''): array
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $questCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('questID_' . $id . '-P_10'  . ($column ? '-Col_' . str_replace(", ", "-", $column) : '')) : false;

        if ($questCache) {
            $quest = $questCache;
        } else {
            $subQ  = $world_db->select("*", false)->where("t1.entry=t2.entry")->get_compiled_select("quest_template t2");
            $quest = $world_db->select($column)->where('entry', $id)->limit(1)->get('quest_template t1')->row_array();

            if ($quest) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('questID_' . $id . '-P_10' . ($column ? '-Col_' . str_replace(", ", "-", $column) : ''), $quest, 60 * 60 * 24 * 30);
                }
            }
        }

        return $quest ?? [];
    }

    public function getQuestTitle(int $id,$realmid, int $patch = 10): string
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $questTitleCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('questNameID_' . $id . '-P_10') : false;

        if ($questTitleCache) {
            $quest_name = $questTitleCache;
        } else {
            $subQ       = $world_db->select("", false)->where("t1.entry=t2.entry")->get_compiled_select("quest_template t2");
            $quest_name = $world_db->select('title')->where('entry', $id)->limit(1)->get('quest_template t1')->row('title');

            if ($quest_name) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('questNameID_' . $id . '-P_10', $quest_name, 60 * 60 * 24 * 30);
                }
            }
        }

        return $quest_name ?? ('Unknown - ' . $id);
    }

    /**
     * @param  int  $id
     * @param  int  $patch
     *
     * @return string
     */
    public function getIconName(int $id, $realmid ,int $patch = 10): string
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        
        $iconCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('iconID' . $id . '-P_10') : false;

        if ($iconCache) {
            $icon = $iconCache;
        } else {
            $subQ = $world_db->select("*", false)->where("t1.entry=t2.entry")->get_compiled_select("item_template t2");

            $world_db->select('i.icon');
            $world_db->from('item_display_info i');
            $world_db->join('item_template t1', 'i.ID = t1.displayid', 'left');
            $world_db->where('t1.entry', $id);
            $world_db->limit(1);
            $icon = $world_db->get()->row('icon');

            if (empty($icon)) {
                $icon = 'INV_Misc_QuestionMarkz';
            }
            if ($this->wowgeneral->getRedisCMS()) {
                // Cache for 30 day
                $this->cache->redis->save('iconID' . $id . '-P_10' , $icon, 60 * 60 * 24 * 30);
            }
        }

        return $icon;
    }

    /**
     * @param  int  $id
     *
     * @return string
     */
    public function getFactionName(int $id,$realmid): string
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $factionCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('factionID_' . $id) : false;

        if ($factionCache) {
            $faction = $factionCache;
        } else {
            $subQ    = $world_db->select('*')->from('faction')->where('id', $id)->get_compiled_select();
            $faction = $world_db->select('name')->where('id', $id)->limit(1)->get('faction')->row('name');

            if (empty($faction)) {
                $faction = 'Unknown';
            }

            if ($this->wowgeneral->getRedisCMS()) {
                // Cache for 30 day
                $this->cache->redis->save('factionID_' . $id, $faction, 60 * 60 * 24 * 30);
            }
        }

        return $faction;
    }

    public function getZoneName(int $id,int $map,$realmid, string $table = 'creature'): string
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        
        $zone     = "";
        $loc_list = [];
        if ($map < 0 || $id < 0) 
        {    
            return 'Unknown (No Spawn Found)';
        } 
        else if ($map < 2) //Azeroth, Kalimdor
        {
            $coords = $world_db->select('position_x, position_y')->where('id', $id)->limit(1)->get($table)->row_array();
            if ($coords) 
            {
                (int)$pos_x = $coords['position_x'];
                (int)$pos_y = $coords['position_y'];

                foreach ($this->config->item('map_area') as $key => $area) 
                {
                    // MapID, Name, X_Min, Y_Min, X_Max, Y_Max
                    if ($area[0] === $map && $area[2] < $pos_x && $area[4] > $pos_x && $area[3] < $pos_y && $area[5] > $pos_y) 
                    {
                        $filename = 'application/modules/database/assets/images/mbound/' . $key . '.png';

                        if (! file_exists($filename)) 
                        {
                            continue;
                        }

                        $image = imagecreatefrompng($filename);

                        // Coordinates of map
                        $x = 100 - round(($pos_y - $area[3]) / (($area[5] - $area[3]) / 100), 1);
                        $y = 100 - round(($pos_x - $area[2]) / (($area[4] - $area[2]) / 100), 1);

                        if (imagecolorat($image, round($x * 9.99), round($y * 9.99)) !== 0) 
                        {
                            continue;
                        }

                        if ($x && $y) 
                        {
                            $area['x'] = $x;
                            $area['y'] = $y;
                        }
                        $loc_list[] = $area;
                        //$zone       .= $area[1] . ', ';
                    }
                }
                //TODO: Incorrect result may occur
                if ($loc_list) {
                    $area = $loc_list[0];
                    if (count($loc_list) > 1) {
                        foreach ($loc_list as $loc) {
                            $curr = $area;
                            $next = $loc;

                            if (abs($curr[2] - $curr[4]) < abs($next[2] - $next[4]) || abs($curr[3] - $curr[5]) < abs($next[3] - $next[5])) {
                                $area = $loc;
                            }
                        }
                    }
                    $zone = $area[1]; // . ' <small>(' . $area['x'] . ', ' . $area['y'] . ')</small>';
                }
            }
        } else {
            $area = $world_db->select('name')->where('map_id >', 2)->where('map_id', $map)->limit(1)->get('area_template')->row('name');

            if ($area) {
                $zone = $area;
            }
        }

        return ! empty($zone) ? rtrim($zone, ', ') : 'Unknown';
    }



 

    public function getItemRelatedList(int $id,int $realmid ,int $patch = 10, int $limit = 1000): array
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $itemList      = [];
        $itemListCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('itemID_' . $id . '-ItemRelated' . '-P_10' ) : false;

        if ($itemListCache) {
            $itemList = $itemListCache;
        } else {
            $subQ   = $world_db->select("*", false)->where("it.entry=it2.entry")->get_compiled_select("item_template it2");
            $tables = ['contained' => 'item_loot_template', 'disenchanted' => 'disenchant_loot_template'];
            $cols   = ['contained' => 'entry', 'disenchanted' => 'DisenchantID'];

            foreach ($tables as $key => $tbl) {
                $refData = $this->getDropTable($id, $tbl, $realmid);
                if ($refData) {
                    $items = array_keys($refData);

                    $world_db->select('entry, name, class, subclass, Quality, ItemLevel, RequiredLevel, DisenchantID');
                    $world_db->from('item_template it');
                    $world_db->where_in($cols[$key], $items);
                    $world_db->group_by('it.entry');
                    $world_db->limit($limit); //TODO: Call by API with pagination, eliminate this.
                    $res = $world_db->get()->result_array();

                    if ($res) {
                        // Merge the data
                        foreach ($res as $k => $v) {
                            foreach ($refData[$v[$cols[$key]]] as $rk => $rv) {
                                $res[$k][$rk] = $rv;
                            }
                        }

                        $itemList[$key] = $res;
                        unset($creatures, $refData);

                        if ($this->wowgeneral->getRedisCMS()) {
                            // Cache for 30 day
                            $this->cache->redis->save('itemID_' . $id . '-ItemRelated' . '-P_' . $patch, $itemList, 60 * 60 * 24 * 30);
                        }
                    }
                }
            }
        }

        return $itemList;
    }

    // A. Blohmé, 24.08.24
    // Get spawn location and stuff for creature (it doesnt work inside item db yet!)
    public function getCreatureRelatedList(int $id, $realmid,int $patch = 10, int $limit = 1000): array
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $dropList      = [];
        $dropListCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('itemID_' . $id . '-CreatureRelated' . '-P_10') : false;
        //echo "checking drop cdhance...";
        if ($dropListCache) 
        {
            $dropList = $dropListCache;
        } 
        else 
        {
            $subQ   = $world_db->select("*", false)->where("ct.entry=ct2.entry")->get_compiled_select("creature_template ct2");
            $tables = ['drop' => 'creature_loot_template', 'pocket' => 'pickpocketing_loot_template', 'skin' => 'skinning_loot_template'];
            //echo "WE HAVE SUBQ1111!";
            foreach ($tables as $key => $tbl) 
            {
                //echo "going through tables...";
                $refData = $this->getDropTable($id, $tbl, $realmid);
                if ($refData) 
                {
                    //echo "we got ref data!!!";
                    $creatures = array_keys($refData);
                    $subQ2 = $world_db->select("map")->where("id=ct.entry")->limit(1)->get_compiled_select("creature");
                    $world_db->select('Entry, Name, Rank, MinLevel, MaxLevel, (' . $subQ2 . ') as map');
                    $world_db->from('creature_template ct');
                    $world_db->where_in('Entry', $creatures);
                    $world_db->group_by('ct.Entry');
                    $world_db->limit($limit); //TODO: Call by API with pagination, eliminate this.
                    $res = $world_db->get()->result_array();
                    if ($res) 
                    {
                        // Merge the data
                        foreach ($res as $k => $v) 
                        {
                            foreach ($refData[$v['Entry']] as $rk => $rv) 
                            {
                                $res[$k][$rk] = $rv;
                            }
                        }

                        $dropList[$key] = $res;
                        unset($creatures, $refData);
                    }
                }
            }

            if ($this->wowgeneral->getRedisCMS() && $dropList) {
                // Cache for 30 day
                $this->cache->redis->save('itemID_' . $id . '-CreatureRelated' . '-P_' . $patch, $dropList, 60 * 60 * 24 * 30);
            }
        }
        return $dropList;
    }

    public function getGORelatedList(int $id,$realmid, int $patch = 10, int $limit = 1000): array
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $goList      = [];
        $goListCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('itemID_' . $id . '-GORelated' . '-P_10') : false;

        if ($goList) {
            $goList = $goListCache;
        } else {
            $refData = $this->getDropTable($id, 'gameobject_loot_template', $realmid);
            if ($refData) {
                $objects = array_keys($refData);
                $subQ    = $world_db->select("map")->where("id=gt.entry")->limit(1)->get_compiled_select("gameobject");
                $subQ2   = $world_db->select("", false)->where("gt.entry=gt2.entry")->get_compiled_select("gameobject_template gt2");

                $world_db->select('entry, name, type, data0, data1, (' . $subQ . ') as map');
                $world_db->from('gameobject_template gt');
                $world_db->where_in('data1', $objects);
                $world_db->group_by('gt.entry');
                $world_db->limit($limit); //TODO: Call by API with pagination, eliminate this.
                $objList = $world_db->get()->result_array();

                if ($objList) {
                    // Merge the data
                    foreach ($objList as $k => $v) {
                        foreach ($refData[(int)$v['data1']] as $rk => $rv) {
                            $objList[$k][$rk] = $rv;
                        }
                    }

                    unset($creatures, $refData);
                    foreach ($objList as $drop) {
                        (int)$prop = $this->config->item('lock')[$drop['data0']][5] ?? 0;
                        if ($prop === 2) {
                            $goList['gathered'][] = $drop;
                        } elseif ($prop === 3) {
                            $goList['mined'][] = $drop;
                        } else {
                            $goList['contained'][] = $drop;
                        }
                    }
                    unset($objList, $drop, $prop);

                    if ($this->wowgeneral->getRedisCMS()) {
                        // Cache for 30 day
                        $this->cache->redis->save('itemID_' . $id . '-GORelated' . '-P_' . $patch, $goList, 60 * 60 * 24 * 30);
                    }
                }
            }
        }

        return $goList;
    }

    public function getFishedInList(int $id, $realmid,int $patch = 10, int $limit = 1000): array
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $fishedInList  = [];
        $fishedInCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('itemID_' . $id . '-fishedIn' . '-P_10') : false;

        if ($fishedInCache) {
            $fishedInList = $fishedInCache;
        } else {
            $refData = $this->getDropTable($id, 'fishing_loot_template', $realmid);
            if ($refData) {
                $zones = array_keys($refData);
                $world_db->select('entry, map_id, zone_id, area_level, name, team');
                $world_db->from('area_template at');
                $world_db->where_in('entry', $zones);
                $world_db->group_by('at.entry');
                $world_db->limit($limit); //TODO: Call by API with pagination, eliminate this.
                $fishedInList = $world_db->get()->result_array();

                if ($fishedInList) {
                    // Merge the data
                    foreach ($fishedInList as $k => $v) {
                        foreach ($refData[$v['entry']] as $rk => $rv) {
                            $fishedInList[$k][$rk] = $rv;
                        }
                    }
                    unset($refData);

                    if ($this->wowgeneral->getRedisCMS()) {
                        // Cache for 30 day
                        $this->cache->redis->save('itemID_' . $id . '-fishedIn' . '-P_10', $fishedInList, 60 * 60 * 24 * 30);
                    }
                }
            }
        }

        return $fishedInList;
    }

    public function getRewardList(int $id, $realmid,int $patch = 10): array
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $rewardList      = [];
        $rewardListCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('itemID_' . $id . '-RewardList') : false;

        if ($rewardListCache) {
            $rewardList = $rewardListCache;
        } else {


            $world_db->distinct();
            $world_db->select('qt.entry as qentry, qt.Title, qt.MinLevel, qt.QuestLevel, qt.RewMoneyMaxLevel, qt.RewOrReqMoney, t1.name, t1.quality, t1.entry');
            $world_db->from('item_template t1');
            $world_db->join('(SELECT * FROM quest_template) qt', 'qt.RewItemId1 = t1.entry', 'left');
            $world_db->where('t1.entry', $id);
            $world_db->order_by('qt.RewMoneyMaxLevel', 'DESC');
            $world_db->limit(1);
            $rewardList = $world_db->get()->result_array();

            if ($rewardList) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('itemID_' . $id . '-RewardList', $rewardList, 60 * 60 * 24 * 30);
                }
            }
        }

        return $rewardList;
    }

    public function getContainsList(int $id, $realmid,int $patch = 10): array
    {
        $containsList      = [];
        $containsListCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('itemID_' . $id . '-containsIn' . '-P_10') : false;

        if ($containsListCache) {
            $containsList = $containsListCache;
        } else {
            $containsList = $this->getLootTable($id, $realmid,'item_loot_template', $patch = 10, 1);

            if ($containsList) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('itemID_' . $id . '-containsIn' . '-P_10', $containsList, 60 * 60 * 24 * 30);
                }
            }
        }

        return $containsList;
    }

    public function getDisenchantList(int $disenchant_id,$realmid,int $patch = 10): array
    {
        $disenchantList      = [];
        $disenchantListCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('itemID_' . $disenchant_id . '-disenchantsIn' . '-P_10') : false;

        if ($disenchantListCache) {
            $disenchantList = $disenchantListCache;
        } else {
            $disenchantList = $this->getLootTable($disenchant_id,$realmid, 'disenchant_loot_template', $patch = 10, 1);

            if ($disenchantList) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('itemID_' . $disenchant_id . '-disenchantsIn' . '-P_10', $disenchantList, 60 * 60 * 24 * 30);
                }
            }
        }

        return $disenchantList;
    }

    public function getVendorList(int $id, $realmid,int $patch = 10): array
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $vendorList      = [];
        $vendorListCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('itemID_' . $id . '-VendorList') : false;

        if ($vendorListCache) {
            $vendorList = $vendorListCache;
        } else {
            $subQ  = $world_db->select('BuyPrice')->where("entry = nv.item")->limit(1)->get_compiled_select('item_template');
            $subQ2 = $world_db->select('map')->where("id=ct.Entry")->limit(1)->get_compiled_select('creature');
            $subQ3 = $world_db->select("", false)->where("ct.Entry=ct2.Entry")->get_compiled_select("creature_template ct2");

            $world_db->distinct();
            $world_db->select('ct.Entry, ct.Name, ct.SubName, ct.MinLevel, ct.MaxLevel, nv.maxcount, (' . $subQ . ') as buy_price, (' . $subQ2 . ') as map');
            $world_db->from('creature_template ct');
            $world_db->join('(SELECT entry, item, maxcount FROM npc_vendor) nv', 'nv.entry = ct.Entry', 'inner');
            $world_db->where('nv.item', $id);
            $world_db->order_by('nv.entry', 'ASC');
            $world_db->limit(50);
            $vendorList = $world_db->get()->result_array();

            if ($vendorList) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('itemID_' . $id . '-VendorList', $vendorList, 60 * 60 * 24 * 30);
                }
            }
        }

        return $vendorList;
    }

    public function getDropTable(int $item, string $table, $realmid): array
    {
        //$patch;
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $total = 0;

        // Reverse search for loot starting from the reference table
        // Looking for groups
        $drop     = [];
        $curtable = 'reference_loot_template';
        $rows     = $world_db->select('entry, groupid, ChanceOrQuestChance, mincountOrRef, maxcount, condition_id')->where('item', $item)->where('mincountOrRef >', 0)->get($curtable)->result_array();

        while (true) {
            foreach ($rows as $i => $row) {
                $chance = abs($row['ChanceOrQuestChance']);
                $cond   = $row['condition_id'];
                if ($chance == 0) {
                    // Entry from a group with an equal drop chance
                    $zerocount = 0;
                    $chancesum = 0;
                    $subrows   = $world_db->select('ChanceOrQuestChance, mincountOrRef, maxcount, condition_id')->where('entry', $row['entry'])->where('groupid', $row['groupid'])->get($curtable)->result_array();

                    foreach ($subrows as $i => $subrow) {
                        if ($subrow['ChanceOrQuestChance'] == 0) {
                            $zerocount++;
                        } else {
                            $chancesum += abs($subrow['ChanceOrQuestChance']);
                        }
                    }
                    $chance = (100 - $chancesum) / $zerocount;
                }
                $chance   = max($chance, 0);
                $chance   = min($chance, 100);
                $mincount = $row['mincountOrRef'];
                $maxcount = $row['maxcount'];

                if ($mincount < 0) {
                    // Reference link. The probability is based on the one already calculated
                    $num      = $mincount;
                    $mincount = $drop[$num]['mincount'];
                    $chance   = $chance * (1 - pow(1 - $drop[$num]['percent'] / 100, $maxcount));
                    $maxcount = $drop[$num]['maxcount'] * $maxcount;
                }

                // Save the probabilities calculated for these groups (ref records are stored as negative int)
                $num = ($curtable <> $table) ? -$row['entry'] : $row['entry'];
                if (isset($drop[$num])) {
                    // Same element has already fallen in another subgroup, consider the overall probability.
                    $newmin                     = ($drop[$num]['mincount'] < $mincount) ? $drop[$num]['mincount'] : $mincount;
                    $newmax                     = $drop[$num]['maxcount'] + $maxcount;
                    $newchance                  = 100 - (100 - $drop[$num]['percent']) * (100 - $chance) / 100;
                    $drop[$num]['percent']      = $newchance;
                    $drop[$num]['mincount']     = $newmin;
                    $drop[$num]['maxcount']     = $newmax;
                    $drop[$num]['condition_id'] = $cond;
                } else {
                    $drop[$num]                 = array();
                    $drop[$num]['percent']      = $chance;
                    $drop[$num]['mincount']     = $mincount;
                    $drop[$num]['maxcount']     = $maxcount;
                    $drop[$num]['checked']      = false;
                    $drop[$num]['condition_id'] = $cond;

                    //TODO: Pagination
                    if ($num > 0 && ++$total > 250) {
                        break;
                    }
                }
            }

            // Check for at least one unverified ref entry
            $num = 0;
            foreach ($drop as $i => $value) {
                if ($i < 0 && ! $value['checked']) {
                    $num = $i;
                    break;
                }
            }

            if ($num == 0) {
                // Check all items
                if ($curtable != $table) {
                    // Ref table?, check main
                    $curtable = $table;

                    foreach ($drop as $i => $value) {
                        $drop[$i]['checked'] = false;
                    }
                    $rows = $world_db->select('entry, groupid, ChanceOrQuestChance, mincountOrRef, maxcount, condition_id')->where('item', $item)->where('mincountOrRef >', 0)->get($curtable)->result_array();
                } else // Done searching
                {
                    break;
                }
            } else {
                // Unverified element found, check it
                $drop[$num]['checked'] = true;
                $rows                  = $world_db->select('entry, groupid, ChanceOrQuestChance, mincountOrRef, maxcount, condition_id')->where('mincountOrRef', $num)->get($curtable)->result_array();
            }
        }

        // Clean up reference links
        foreach ($drop as $i => $value) {
            if ($i < 0) {
                unset($drop[$i]);
            }
        }

        return $drop;
    }

    public function getLootTable(int $item,$realmid, string $table, int $patch = 10, float $mod = 1.0): array
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $loot   = [];
        $groups = [];
        $cols   = [ // Cols will be different for specific template, later
            'item_loot_template'       => 'name, ItemLevel, RequiredLevel, Quality, class, subclass',
            'disenchant_loot_template' => 'name, ItemLevel, RequiredLevel, Quality, class, subclass',
            'reference_loot_template'  => 'name, ItemLevel, RequiredLevel, Quality, class, subclass'
        ];

        $world_db->select('l.ChanceOrQuestChance, l.mincountOrRef, l.maxcount, l.groupid, l.condition_id, i.entry,' . $cols[$table]);
        $world_db->from($table . ' l');
        $world_db->join('(SELECT * FROM item_template) i', 'l.item=i.entry', 'left');
        $world_db->where('l.entry', $item);
        $world_db->group_by('i.entry');
        $world_db->limit(250);
        $rows = $world_db->get()->result_array();

        $last_group   = 0;
        $lq_eq_chance = 100;

        foreach ($rows as $row) {
            // Not in a group
            if ($row['groupid'] == 0) {
                // Link
                if ($row['mincountOrRef'] < 0) {
                    addLoot($loot, $this->getLootTable(-$row['mincountOrRef'],$realmid, 'reference_loot_template', $patch, abs($row['ChanceOrQuestChance']) / 100 * $row['maxcount'] * $mod));
                } else // Ordinary drop
                {
                    addLoot($loot, array(
                        array_merge(array(
                            'percent'  => max(abs($row['ChanceOrQuestChance']) * $mod, 0) * sign($row['ChanceOrQuestChance']),
                            'mincount' => $row['mincountOrRef'],
                            'maxcount' => $row['maxcount']
                        ),
                            $row
                        )
                    ));
                }
            } // In group
            else {
                $chance = $row['ChanceOrQuestChance'];

                if ($row['groupid'] <> $last_group) {
                    $last_group   = $row['groupid'];
                    $lq_eq_chance = 100;
                }

                if ($chance > 0) {
                    $lq_eq_chance -= $chance;
                    $lq_eq_chance = max($lq_eq_chance, 0);

                    if ($row['mincountOrRef'] < 0) {
                        addLoot($loot, $this->getLootTable(-$row['mincountOrRef'],$realmid, 'reference_loot_template', $patch, $chance / 100 * $row['maxcount'] * $mod));
                    } else {
                        addLoot($loot, array(
                            array_merge(array(
                                'percent'  => $chance * $mod,
                                'mincount' => $row['mincountOrRef'],
                                'maxcount' => $row['maxcount'],
                                'group'    => $row['groupid'],
                            ),
                                $row
                            )
                        ));
                    }
                } else {
                    $groups[$last_group][] = array_merge(array(
                        'mincount'    => $row['mincountOrRef'],
                        'maxcount'    => $row['maxcount'],
                        'groupchance' => $lq_eq_chance * $mod
                    ),
                        $row
                    );
                }
            }
        }

        // Add groups
        foreach ($groups as $group) {
            $num = count($group);
            foreach ($group as $item) {
                $item['percent'] = $item['groupchance'] / $num;
                addLoot($loot, array($item));
            }
        }

        return $loot;
    }

    /**
     * @param  int  $id
     *
     * @return array
     */
    public function findConditionByID(int $id, $realmid): array
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        $condition      = '';
        $conditionCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('conditionID_' . $id) : false;

        if ($conditionCache) {
            $condition = $conditionCache;
        } else {
            if ($id > 0) {
                $condition = $world_db->where('condition_entry', $id)->get('conditions')->row_array();
            }

            if ($condition) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('conditionID_' . $id, $condition, 60 * 60 * 24 * 30);
                }
            }
        }

        return $condition;
    }

    /**
     * @param  array  $condition
     * @param  bool   $targetsSwapped
     *
     * @return string
     */
    public function describeCondition(array $condition, bool $targetsSwapped,$realmid): string
    {
        $conditionDescCache = $this->wowgeneral->getRedisCMS() ? $this->cache->redis->get('conditionDescArrID_' . $condition['condition_entry']) : false;

        if ($conditionDescCache) {
            $description = $conditionDescCache;
        } else {
            $description = $condition['condition_entry'] . ": ";
            if (($condition['flags'] & 1) != 0) // reverse result
            {
                $description .= " Not (";
            }
            if (($condition['flags'] & 2) != 0) // swap targets
            {
                $targetsSwapped = ! $targetsSwapped;
            }
            $sourceName = $targetsSwapped ? "Target" : "Source";
            $targetName = $targetsSwapped ? "Source" : "Target";
            switch ($condition['type']) {
                case -3: // Not
                {
                    $description .= "Not (";
                    $reference   = $this->findConditionByID((int)$condition['value1'],$realmid);
                    if ($reference != null) {
                        $description .= $this->describeCondition($reference, $targetsSwapped,$realmid);
                    } else {
                        $description .= "Invalid condition " . $condition['value1'];
                    }
                    $description .= ")";
                    break;
                }
                case -2: // Or
                {
                    $description .= "(";
                    $reference1  = $this->findConditionByID((int)$condition['value1'],$realmid);
                    if ($reference1 != null) {
                        $description .= $this->describeCondition($reference1, $targetsSwapped,$realmid);
                    } else {
                        $description .= "Invalid condition " . $condition['value1'];
                    }
                    $description .= ") Or (";
                    $reference2  = $this->findConditionByID((int)$condition['value2'],$realmid);
                    if ($reference2 != null) {
                        $description .= $this->describeCondition($reference2, $targetsSwapped,$realmid);
                    } else {
                        $description .= "Invalid condition " . $condition['value2'];
                    }
                    $description .= ")";
                    if ($condition['value3'] != 0) {
                        $description .= " Or (";
                        $reference3  = $this->findConditionByID((int)$condition['value3'],$realmid);
                        if ($reference3 != null) {
                            $description .= $this->describeCondition($reference3, $targetsSwapped,$realmid);
                        } else {
                            $description .= "Invalid condition " . $condition['value3'];
                        }
                        $description .= ")";
                    }
                    if ($condition['value4'] != 0) {
                        $description .= " Or (";
                        $reference4  = $this->findConditionByID((int)$condition['value4'],$realmid);
                        if ($reference4 != null) {
                            $description .= $this->describeCondition($reference4, $targetsSwapped,$realmid);
                        } else {
                            $description .= "Invalid condition " . $condition['value4'];
                        }
                        $description .= ")";
                    }
                    break;
                }
                case -1: // And
                {
                    $description .= "(";
                    $reference1  = $this->findConditionByID((int)$condition['value1'],$realmid);
                    if ($reference1 != null) {
                        $description .= $this->describeCondition($reference1, $targetsSwapped,$realmid);
                    } else {
                        $description .= $condition['value1'] . ": Invalid condition";
                    }
                    $description .= ") And (";
                    $reference2  = $this->findConditionByID((int)$condition['value2'],$realmid);
                    if ($reference2 != null) {
                        $description .= $this->describeCondition($reference2, $targetsSwapped,$realmid);
                    } else {
                        $description .= $condition['value2'] . ": Invalid condition";
                    }
                    $description .= ")";
                    if ($condition['value3'] != 0) {
                        $description .= " And (";
                        $reference3  = $this->findConditionByID((int)$condition['value3'],$realmid);
                        if ($reference3 != null) {
                            $description .= $this->describeCondition($reference3, $targetsSwapped,$realmid);
                        } else {
                            $description .= $condition['value3'] . ": Invalid condition";
                        }
                        $description .= ")";
                    }
                    if ($condition['value4'] != 0) {
                        $description .= " And (";
                        $reference4  = $this->findConditionByID((int)$condition['value4'],$realmid);
                        if ($reference4 != null) {
                            $description .= $this->describeCondition($reference4, $targetsSwapped,$realmid);
                        } else {
                            $description .= $condition['value4'] . ": Invalid condition";
                        }
                        $description .= ")";
                    }
                    break;
                }
                case 0: // None
                {
                    $description .= "Always True";
                    break;
                }
                case 1: // Aura
                {
                    $description .= $targetName . " Has Aura " . $condition['value1'] . " Index " . $condition['value2'];
                    break;
                }
                case 2: // Item
                {
                    $description .= $targetName . " Has " . $condition['value2'] . " Stacks Of Item " . $condition['value1'] . " In Inventory";
                    break;
                }
                case 3: // Item Equipped
                {
                    $description .= $targetName . " Has Equipped Item " . $condition['value1'];
                    break;
                }
                case 4: // Area Id
                {
                    $description .= $sourceName . " or " . $targetName . " Is In Zone or Area " . $condition['value1'];
                    break;
                }
                case 5: // Reputation Rank Min
                {
                    $description .= $targetName . " Is " . getRepRank((int)$condition['value2']) . " Or Better With Faction " . $condition['value1'];
                    break;
                }
                case 6: // Team
                {
                    $description .= $targetName . " Is Team " . getTeamName((int)$condition['value1']);
                    break;
                }
                case 7: // Skill
                {
                    $description .= $targetName . " Has " . $condition['value2'] . " Points In Skill " . $condition['value1'];
                    break;
                }
                case 8: // Quest Rewarded
                {
                    $description .= $targetName . " Has Done Quest " . $condition['value1'];
                    break;
                }
                case 9: // Quest Taken
                {
                    $status = "";
                    if ($condition['value2'] == 1) // Incomplete
                    {
                        $status = "Incomplete ";
                    } elseif ($condition['value2'] == 2) // Complete
                    {
                        $status = "Complete ";
                    }
                    $description .= $targetName . " Has " . $status . "Quest " . $condition['value1'] . " In Log";
                    break;
                }
                case 10: // AD Commission Aura
                {
                    $description .= $targetName . " Has Argent Dawn Commission";
                    break;
                }
                case 11: // Saved Variable
                {
                    $description .= "Saved Variable In Index " . $condition['value1'] . " Is " . getComparisonOperatorName($condition['value3']) . " " . $condition['value2'];
                    break;
                }
                case 12: // Active Game Event
                {
                    $description .= "Game Event " . $condition['value1'] . " Is Active";
                    break;
                }
                case 13: // Can't Path To Victim
                {
                    $description .= $sourceName . " Can't Path To Victim";
                    break;
                }
                case 14: // Race Class
                {
                    $description .= $targetName . " ";
                    if ($condition['value1'] == 0 && $condition['value2'] == 0) {
                        $description .= "Is Any Race or Class";
                    } else {
                        if ($condition['value1'] != 0) {
                            $description .= "Is Race (" . getAllowableRace((int)$condition['value1']) . ")";
                            if ($condition['value2'] != 0) {
                                $description .= " And ";
                            }
                        }
                        if ($condition['value2'] != 0) {
                            $description .= "Is Class (" . className((int)$condition['value2']) . ")";
                        }
                    }
                    break;
                }
                case 15: // Level
                {
                    $description .= $targetName . "'s Level Is " . getComparisonOperatorName($condition['value2']) . " " . $condition['value1'];
                    break;
                }
                case 16: // Source Entry
                {
                    $description .= $sourceName . "'s Entry Is " . $condition['value1'];
                    if ($condition['value2'] != 0) {
                        $description .= " Or " . $condition['value2'];
                    }
                    if ($condition['value3'] != 0) {
                        $description .= " Or " . $condition['value3'];
                    }
                    if ($condition['value4'] != 0) {
                        $description .= " Or " . $condition['value4'];
                    }
                    break;
                }
                case 17: // Spell
                {
                    $description .= $targetName . " Knows Spell " . $condition['value1'];
                    break;
                }
                case 18: // Instance Script
                {
                    $description .= "Hardcoded condition " . $condition['value2'] . " For Map " . $condition['value1'];
                    break;
                }
                case 19: // Quest Available
                {
                    $description .= $targetName . " Can Accept Quest " . $condition['value1'];
                    break;
                }
                case 20: // Nearby Creature
                {
                    $description .= "Creature " . $condition['value1'] . " Is " . ($condition['value3'] == 0 ? "Alive" : "Dead") . " Within " . $condition['value2'] . " Yards Of The " . $targetName;
                    if ($condition['value4'] != 0) {
                        $description .= " But It's Not The " . $targetName;
                    }
                    break;
                }
                case 21: // Nearby GameObject
                {
                    $description .= "GameObject " . $condition['value1'] . " Is Within " . $condition['value2'] . " Yards Of The " . $targetName;
                    break;
                }
                case 22: // Quest None
                {
                    $description .= $targetName . " Has Not Accepted or Completed Quest " . $condition['value1'];
                    break;
                }
                case 23: // Item With Bank
                {
                    $description .= $targetName . " Has " . $condition['value2'] . " Stacks of Item " . $condition['value1'] . " In Inventory Or Bank";
                    break;
                }
                case 24: // WoW Patch
                {
                    $description .= "Content Patch Is " . getComparisonOperatorName($condition['value2']) . " 1." . ($condition['value1'] + 2);
                    break;
                }
                case 25: // Escort
                {
                    $tmp = "";
                    if (($condition['value1'] & 1) != 0) // CF_ESCORT_Source_DEAD
                    {
                        $tmp .= $sourceName . " Is Dead";
                    }
                    if (($condition['value1'] & 2) != 0) // CF_ESCORT_TARGET_DEAD
                    {
                        if (! strlen($tmp) == 0) {
                            $tmp .= " Or ";
                        }
                        $tmp .= $targetName . " Is Dead";
                    }
                    if ($condition['value2'] != 0) {
                        if (! strlen($tmp) == 0) {
                            $tmp .= " Or ";
                        }
                        $tmp .= $sourceName . " Is Not Within " . $condition['value2'] . " Yards Of " . $targetName;
                    }
                    $description .= $tmp;
                    break;
                }
                case 26: // Active Holiday
                {
                    $description .= "Holiday " . $condition['value1'] . " Is Active";
                    break;
                }
                case 27: // Gender
                {
                    $description .= $targetName . " Is Gender " . getGenderName((int)$condition['value1']);
                    break;
                }
                case 28: // Is Player
                {
                    $description .= $targetName . " Is Player";
                    if ($condition['value1'] != 0) {
                        $description .= " Or Player Controlled";
                    }
                    break;
                }
                case 29: // Skill Below
                {
                    $description .= $targetName . " Has Less Than " . $condition['value2'] . " Points In Skill " . $condition['value1'];
                    break;
                }
                case 30: // Reputation Rank Max
                {
                    $description .= $targetName . " Is " . getRepRank((int)$condition['value2']) . " Or Worse With Faction " . $condition['value1'];
                    break;
                }
                case 31: // Has Flag
                {
                    $description .= $sourceName . " Has Flag " . $condition['value2'] . " In Field " . $condition['value1'];
                    break;
                }
                case 32: // Last Waypoint
                {
                    $description .= $sourceName . "'s Last Reached Waypoint Is " . getComparisonOperatorName($condition['value2']) . " " . $condition['value1'];
                    break;
                }
                case 33: // Map Id
                {
                    $description .= "Current Map Id Is " . $condition['value1'];
                    break;
                }
                case 34: // Instance Data
                {
                    $description .= "Stored Value In Index " . $condition['value1'] . " From Instance Script Is " . getComparisonOperatorName($condition['value3']) . " " . $condition['value2'];
                    break;
                }
                case 35: // Map Event Data
                {
                    $description .= "Stored Value In Index " . $condition['value2'] . " From Scripted Map Event " . $condition['value1'] . " Is " . getComparisonOperatorName($condition['value4']) . " " . $condition['value3'];
                    break;
                }
                case 36: // Map Event Active
                {
                    $description .= "Scripted Map Event " . $condition['value1'] . " Is Active";
                    break;
                }
                case 37: // Line Of Sight
                {
                    $description .= "Targets Are In Line Of Sight";
                    break;
                }
                case 38: // Distance To Target
                {
                    $description .= "Distance Between Targets Is " . getComparisonOperatorName($condition['value2']) . " " . $condition['value1'] . " Yards";
                    break;
                }
                case 39: // Is Moving
                {
                    $description .= $targetName . " Is Moving";
                    break;
                }
                case 40: // Has Pet
                {
                    $description .= $targetName . " Has A Pet";
                    break;
                }
                case 41: // Health Percent
                {
                    $description .= $targetName . "'s Health Is " . getComparisonOperatorName($condition['value2']) . " " . $condition['value1'] . " Percent";
                    break;
                }
                case 42: // Mana Percent
                {
                    $description .= $targetName . "'s Mana Is " . getComparisonOperatorName($condition['value2']) . " " . $condition['value1'] . " Percent";
                    break;
                }
                case 43: // Is In Combat
                {
                    $description .= $targetName . " Is In Combat";
                    break;
                }
                case 44: // Is Hostile To
                {
                    $description .= $targetName . " Is Hostile To " . $sourceName;
                    break;
                }
                case 45: // Is In Group
                {
                    $description .= $targetName . " Is In Group";
                    break;
                }
                case 46: // Is Alive
                {
                    $description .= $targetName . " Is Alive";
                    break;
                }
                case 47: // Map Event Targets
                {
                    $description .= "Extra Targets Of Scripted Map Event " . $condition['value1'] . " Satisfy condition (";
                    $reference   = $this->findConditionByID((int)$condition['value2'],$realmid);
                    if ($reference != null) {
                        $description .= $this->describeCondition($reference, $targetsSwapped,$realmid);
                    } else {
                        $description .= $condition['value2'] . ": Invalid condition";
                    }
                    $description .= ")";
                    break;
                }
                case 48: // Object Is Spawned
                {
                    $description .= $targetName . " GameObject Is Spawned";
                    break;
                }
                case 49: // Object Loot State
                {
                    $description .= $targetName . " GameObject's Loot State Is " . getLootStateName((int)$condition['value1']);
                    break;
                }
                case 50: // Object Fit $condition
                {
                    $description .= "GameObject With Guid " . $condition['value1'] . " Satisfies condition (";
                    $reference   = $this->findConditionByID((int)$condition['value2'],$realmid);
                    if ($reference != null) {
                        $description .= $this->describeCondition($reference, $targetsSwapped,$realmid);
                    } else {
                        $description .= $condition['value2'] . ": Invalid condition";
                    }
                    $description .= ")";
                    break;
                }
                case 51: // PVP Rank
                {
                    $description .= $targetName . "'s PvP Rank Is " . getComparisonOperatorName($condition['value2']) . " " . $condition['value1'];
                    break;
                }
                case 52: // DB Guid
                {
                    $description .= $sourceName . "'s Guid Is " . $condition['value1'];
                    if ($condition['value2'] != 0) {
                        $description .= " Or " . $condition['value2'];
                    }
                    if ($condition['value3'] != 0) {
                        $description .= " Or " . $condition['value3'];
                    }
                    if ($condition['value4'] != 0) {
                        $description .= " Or " . $condition['value4'];
                    }
                    break;
                }
                case 53: // Local Time
                {
                    $description .= "Current Time Is Between " . $condition['value1'] . ":" . $condition['value2'] . " And " . $condition['value3'] . ":" . $condition['value4'];
                    break;
                }
                case 54: // Distance To Position
                {
                    $description .= $targetName . " Is Within " . $condition['value4'] . " Yards Of X " . $condition['value1'] . " Y " . $condition['value2'] . " Z " . $condition['value3'];
                    break;
                }
                case 55: // Object GO State
                {
                    $description .= $targetName . " GameObject's GO State Is " . getGOStateName((int)$condition['value1']);
                    break;
                }
                case 56: // Nearby Player
                {
                    $description .= nearByPlayer($condition['value1']) . " Player Within " . $condition['value2'] . " Yards Of The " . $targetName;
                    break;
                }
            }

            if ($description) {
                if ($this->wowgeneral->getRedisCMS()) {
                    // Cache for 30 day
                    $this->cache->redis->save('conditionDescArrID_' . $condition['condition_entry'], $description, 60 * 60 * 24 * 30);
                }
            }
        }

        return $description . ((($condition['flags'] & 1) != 0) ? ")" : "");
    }

    public function searchItem(string $entry, $realmid,int $patch = 10, int $limit = 0): array
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
            break;
        
            case 1:
                $world_db = $this->world_tbc;
            break;
        }
        // Show only the most recent version

        $world_db->select('entry, name, Quality, class, subclass, ItemLevel, RequiredLevel, InventoryType');

        if (ctype_digit($entry)) {
            $world_db->where('entry', $entry);
        } else {
            $world_db->like('name', $entry);
        }

        if ($limit > 0) {
            $world_db->limit($limit);
        }
        $world_db->order_by('Quality', 'DESC');
        $res = $world_db->get('item_template t1')->result_array() ?? [];

        if ($res) {
            foreach ($res as $key => $val) {
                $res[$key]['icon'] = $this->getIconName($val['entry'],$realmid, 10);
            }
        }

        return $res;
    }

    public function searchSpell(string $entry, $realmid,int $patch = 10, int $limit = 0): array
    {
        $isExp = $this->wowrealm->isTbc($realmid);                
        switch ($isExp) 
        {
            case 0:
                $world_db = $this->world;
			$world_db->select('Id, SpellName, Rank1, BaseLevel, School, SpellIconID');

            break;
        
            case 1:
                $world_db = $this->world_tbc;
			$world_db->select('Id, SpellName, Rank1, BaseLevel, SchoolMask, SpellIconID');

            break;
        }

        // Show only the most recent version


		
        
        if (ctype_digit($entry)) {
            $world_db->where('Id', $entry);
        } else {
            $world_db->like('SpellName', $entry);
        }
        if ($limit > 0) {
            $world_db->limit($limit);
        }

        $res = $world_db->get('spell_template t1')->result_array() ?? [];


        if ($res) {
            foreach ($res as $key => $val) {
                $res[$key]['icon'] = $this->config->item('spell_icons')[$val['SpellIconID']] ?? 'Trade_Engineering';
            }
        }

        return $res;
    }
}

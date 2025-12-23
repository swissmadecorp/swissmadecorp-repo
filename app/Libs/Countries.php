<?php

namespace App\Libs;
use App\Models\Country;
use App\Models\State;

class Countries
{
    public function getAllCountries($id = 0, $prefix = 'b_') {
        $countries = Country::All();
        ob_start();
        
        if ($id == 0)
            $id=231;
        
        ?>

        <select class="form-control" name="<?= $prefix ?>country" id="<?= $prefix ?>country-input" >
            <?php foreach ($countries as $country) { ?>
                <option <?php echo $country->id==$id  ? 'selected' : '' ?> value="<?= $country->id ?>"><?= $country->name ?></option>
            <?php } ?>
        </select>


        <?php

        $content = ob_get_clean();
        return $content;
        
    }
    
    public function getAllStates($id = 0,$prefix='b_') {
        $states = State::where('country_id','231')->get();
        ob_start();
        
        ?>
        <select class="form-control" name="<?= $prefix ?>state" id="<?= $prefix ?>state-input" >
            <option value="0"></option>
            <?php foreach ($states as $state) { ?>
                <option value="<?= $state->id ?>"<?php echo $id==$state->id ? 'selected' : '' ?>><?= $state->name ?></option>
            <?php } ?>
        </select>


        <?php

        $content = ob_get_clean();
        return $content;
        
    }

    public function getCountryBySortName($countryName) {
        $country = Country::where('sortname',$countryName)->first();
        return $country->id;
    }
    
    public function getStateByName($stateName,$countryId) {
        $state = State::where('name',$stateName)
            ->where('country_id',$countryId)
            ->first();
        
        if (!$state)
            $state = State::create([
                'name' => $stateName,
                'country_id' => $countryId
            ]);
        
        return $state->id;
    }

    public function getCountry($id) {
        if (!$id) return '';

        $country = Country::find($id);
        return $country->name;
    }

    public function getStateFromCountry($id) {
        if (!$id) return '';

        $state = State::find($id);
        if (!$state)
            return '';

        return $state->name;
    }

    public function getStateCodeFromCountry($id) {
        if (!$id) return '';
        
        $state = State::find($id);
        
        if ($state)
            if ($state->code)
                return $state->code;
            else return $state->name;
        else 
            return $this->getStateFromCountry($id);

    }

    public function getStateByCode($id) {
        if (!$id) return '';
        
        $state = State::where('code',$id)->first();
        
        if ($state)
            return $state->id;
        else 
            return $this->getStateFromCountry($id);

    }
}

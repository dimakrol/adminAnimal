<?php

namespace App\ImportExport;

use App\Contracts\ImportExport\EvansImporter as EvansImporterContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use App\Models\RabbitBreeder;
use Carbon\Carbon;
use phpQuery;
use Auth;
use Log;

class EvansImporter implements EvansImporterContract
{
	public function parseBreeders(UploadedFile $file)
	{
		$breeders = new Collection();
		phpQuery::newDocumentFileHTML($file->path());
		$titleElement = pq('td.PedTitleFrame')->find('h1:eq(1)');
		$titleTxt = pq($titleElement)->text();
		$title = trim(str_replace('PEDIGREE', '', $titleTxt));
		$elements = [[], [], [], []];
		$notesElements = [[], [], [], []];
		$trs = pq('tr');
		$cnt = 0;
		
		$keyNotesArray = [];
		foreach ($trs as $tr)
		{
			$tds = pq($tr)->find('td');
			for($i = 0; $i < count($tds); $i++)
			{
				$td = $tds[$i];
				
				$emp = str_replace(array("\n", "<br>", "<br />"), "",preg_replace('/\s\s+/', '', pq($td)->html())); 
				$empRaw = pq($td)->html(); 
				
				if($emp != ""){
					if($cnt == 17 || $cnt == 9 || $cnt == 25|| $cnt == 5 || $cnt == 13 || $cnt == 21 || $cnt == 29 || $cnt == 3 || $cnt == 7 || $cnt == 11 || $cnt == 15 || $cnt == 19 || $cnt == 23 || $cnt == 27 || $cnt == 31){
						if(str_replace("\xC2\xA0", "", $emp) == '') {
							$keyNotesArray[$cnt][] = "";
						} else {
							$keyNotesArray[$cnt][] = $empRaw;
						}
					}
				}
				
				if(pq($td)->hasClass('male') || pq($td)->hasClass('female') || pq($td)->hasClass('neuter'))
				{
					if($cnt == 14)
					{
						$elements[0][] = $td;
					}
					else if($cnt == 6 || $cnt == 22)
					{
						$elements[1][] = $td;
					}
					else if($cnt == 2 || $cnt == 10 || $cnt == 18 || $cnt == 26)
					{
						$elements[2][] = $td;
					}
					else if($cnt == 0 || $cnt == 4 || $cnt == 8 || $cnt == 12 || $cnt == 16 || $cnt == 20 || $cnt == 24 || $cnt == 28)
					{
						$elements[3][] = $td;
					}
				}
			}
			
			
			$cnt++;
		}
		
		$noteLoopArray = ['17','9','25','5','13','21','29','3','7','11','15','19','23','27','31'];

		foreach($noteLoopArray as $noteLoop){
			if($noteLoop == 17){
				//if (array_key_exists($noteLoop,$keyNotesArray)){
					$notesElements[0][] = $keyNotesArray[$noteLoop][0];
			}
			else if($noteLoop == 9 || $noteLoop == 25){
				if($noteLoop == 9){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[1][0] = $keyNotesArray[$noteLoop][0];
				}
				elseif($noteLoop == 25){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[1][1] = $keyNotesArray[$noteLoop][0];
				}
				
			}
			else if($noteLoop == 5 || $noteLoop == 13 || $noteLoop == 21 || $noteLoop == 29){
				if($noteLoop == 5){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[2][0] = $keyNotesArray[$noteLoop][0];
				}
				elseif($noteLoop == 13){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[2][1] = $keyNotesArray[$noteLoop][0];
				}
				elseif($noteLoop == 21){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[2][2] = $keyNotesArray[$noteLoop][0];
				}
				elseif($noteLoop == 29){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[2][3] = $keyNotesArray[$noteLoop][0];
				}
			}
			else if($noteLoop == 3 || $noteLoop == 7 || $noteLoop == 11 || $noteLoop == 15 || $noteLoop == 19 || $noteLoop == 23 || $noteLoop == 27 || $noteLoop == 31){
				if($noteLoop == 3){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[3][0] = $keyNotesArray[$noteLoop][0];
				}
				elseif($noteLoop == 7){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[3][1] = $keyNotesArray[$noteLoop][0];
				}
				elseif($noteLoop == 11){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[3][2] = $keyNotesArray[$noteLoop][0];
				}
				elseif($noteLoop == 15){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[3][3] = $keyNotesArray[$noteLoop][0];
				}
				elseif($noteLoop == 19){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[3][4] = $keyNotesArray[$noteLoop][0];
				}
				elseif($noteLoop == 23){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[3][5] = $keyNotesArray[$noteLoop][0];
				}
				elseif($noteLoop == 27){
					//if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[3][6] = $keyNotesArray[$noteLoop][0];
				}
				elseif($noteLoop == 31){
					if (array_key_exists($noteLoop,$keyNotesArray)){
						$notesElements[3][7] = $keyNotesArray[$noteLoop][0];
					}
					else{
						$notesElements[3][7] = '';
					}
				}
			}
		}
		for($i = 0; $i < count($elements); $i++)
		{
			$elementGroup = $elements[$i];
			$noteElementGroup = $notesElements[$i];
			$gend = 1;
			for($j = 0; $j < count($elementGroup); $j++)
			{
				$element = $elementGroup[$j];
				$noteElement = $noteElementGroup[$j];
				if(pq($element)->hasClass('male') || pq($element)->hasClass('female'))
				{
					$father = null;
					$mother = null;
					if($i < (count($elements) - 1))
					{
						$parents = [ $elements[$i+1][$j*2], $elements[$i+1][($j*2)+1] ];
						$notesElementparents = [ $notesElements[$i+1][$j*2], $notesElements[$i+1][($j*2)+1] ];

						for ($k = 0; $k < 2; $k++)
						{
							$parent = $parents[$k];
							$noteElementparent = $notesElementparents[$k];
							if(pq($parent)->hasClass('male'))
							{
								//, 'g' . ($i+1) . 'm' . $gend
								$father = $this->parseBreeder($title, $parent, $noteElementparent);
							}
							else if(pq($parent)->hasClass('female'))
							{
								//, 'g' . ($i+1) . 'f' . $gend
								$mother = $this->parseBreeder($title, $parent, $noteElementparent);
							}
						}
					}
					if(pq($element)->hasClass('male')) {
						$breeder = $this->parseBreeder($title, $element, $noteElement, 'g' . ($i+1) . '.f' . $gend);
					} else if(pq($element)->hasClass('female')) {
						$breeder = $this->parseBreeder($title, $element, $noteElement, 'g' . ($i+1) . '.m' . $gend);
					}
					
					if(!empty($father))
					{
						$breeder->father_name = $father->name;
					}
					if(!empty($mother))
					{
						$breeder->mother_name = $mother->name;
					}
					$breeders->push($breeder);
				}
				if(($j+1)%2==0)
				{
					$gend++;
				}
			}
		}
		// $breeders = $breeders;
		return $breeders;
	}
	
	function parseBreeder($title, $element, $noteElementParent, $genme=null)
	{
		$breeder = new RabbitBreeder();
		$breeder->breed = $title;
		if(pq($element)->hasClass('male') == true)
		{
			$breeder->sex = 'buck';
		}
		else if(pq($element)->hasClass('female') == true)
		{
			$breeder->sex = 'doe';
		}
		if($genme!=null) {
			if($genme == 'g1.m1' || $genme == 'g1.f1')
			{
				$breeder->level = 'me';
			} else {
				$breeder->level = $genme;
			}
		}
		$breeder->notes = $noteElementParent;
		
		$elementHtml = pq($element)->html();
		
		$lines = explode('<br>', $elementHtml);
		
		if(count($lines) > 7){
			$removeLines = count($lines) - 7;
			$s = 0;
			for($j = $removeLines; $j <= count($lines)-1; $j++){
				$lines[$s] = $lines[$j];
				$s++;
			}
		}
		
		for ($i = 0; $i < count($lines); $i++)
		{
			$line = $lines[$i];
			if($i == 0)
			{
				$breeder->name = trim($line);
			}
			if($i == 1)
			{
				$breeder->color = trim($line);
			}
			if($i == 2)
			{
				$lineParts = explode('Reg #:', $line);
				if(count($lineParts) > 1)
				{
					$breeder->registration_number = trim($lineParts[1]);
				}
					
				$earLine = trim($lineParts[0]);
				$lineParts = explode('Ear #:', $earLine);
				
				if(count($lineParts) > 1)
				{
					$breeder->tattoo = trim($lineParts[1]);
					$tattooRaw = $lineParts[1];
				} else {
					$lineParts = explode('Ear # :', $earLine);
					if(count($lineParts) > 1)
					{
						$breeder->tattoo = trim($lineParts[1]);
						$tattooRaw = $lineParts[1];
					}
				}
				$tattooRawArr = explode('Reg #', $tattooRaw);

				if(count($tattooRawArr) > 1)
				{
					$breeder->tattoo = trim($tattooRawArr[0]);
				}
			}
			if($i == 3)
			{
				$lineParts = explode('Weight:', $line);
				if(count($lineParts) > 1)
				{
					$weightStr = trim($lineParts[1]);
					if(trim($weightStr) != '' && strpos($weightStr, '#') === false) {
						$weight = floatval($weightStr) * 16;
					} else {
						$weightStr = trim(str_replace('oz', '', $weightStr));
						$weightParts = explode('#', $weightStr);
						if(!isset($weightParts[1]) || trim($weightParts[1]) == '') {
						 	$weightParts[1] = 0;
						}
						$weight = (intval($weightParts[0]) * 16) + intval($weightParts[1]);
						
					}
					$weightUnit = Auth::user()->general_weight_units;
					$weightUnit = empty($weightUnit) ? 'Ounces' : $weightUnit;
					if($weightUnit == 'Ounces')
					{
					}
					else if($weightUnit == 'Pounds')
					{
						$weight = $weight/16;
					}
					else if($weightUnit == 'Pound/Ounces')
					{
					}
					else if($weightUnit == 'Grams')
					{
						$weight = $weight * 28.3495;
					}
					else if($weightUnit == 'Kilograms')
					{
						$weight = ($weight * 28.3495) / 1000;
					}
					if($weight!=0) {
						$breeder->weight = round($weight, 3);
					} else {
						$breeder->weight = '';
					}
				}
			}
			if($i == 4)
			{
				$lineParts = explode('Legs:', $line);
				if(count($lineParts) > 0)
				{
					$gcPart = trim($lineParts[0]);
					$gcParts = explode('GC:', $gcPart);
					if(count($gcParts) > 1)
					{
						$gcPart = trim($gcParts[1]);
						$breeder->champion_number = $gcPart;
					}
				}
				if(count($lineParts) > 1)
				{
					$legsPart = trim($lineParts[1]);
					$breeder->legs = $legsPart;
				}
			}
			if($i == 5)
			{
				$lineParts = explode('DOB:', $line);
				if(count($lineParts) > 1)
				{
					// $dobStr = trim($lineParts[1]);
					$dobStr = str_replace("\xC2\xA0", ' ', $lineParts[1]);
					$dobStr = trim($dobStr);
					if(!empty($dobStr))
					{
						try {
							$dobStr = Carbon::createFromFormat('Y F d', $dobStr);
						} catch(\Exception $e) {
						}
						$dobStr = Carbon::parse($dobStr);

						if(Auth::user()->date_format == 'US')
						{
							$dobStr = $dobStr->format('m/d/Y');
						}
						else if(Auth::user()->date_format == 'INT')
						{
							$dobStr = $dobStr->format('d/m/Y');
						}
						else 
						{
							$dobStr = $dobStr->format('d/m/Y');
						}
					}
					$breeder->date_of_birth = $dobStr;
				}
			}
		}
		return $breeder;
	}
}

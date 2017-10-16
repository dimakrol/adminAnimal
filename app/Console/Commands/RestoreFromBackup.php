<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\AdminPedigreesController;
use App\Models\Pedigree;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RestoreFromBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore from barntrax 7_6';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pedigrees = Pedigree::where(function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'Dunbar')
                    ->where('custom_id', '14');
            }) // 11
            ->orWhere(function ($q) {
                $q->where('name', 'pepper')
                    ->where('custom_id', '08');
            });
        })->where(function ($query) {
            $query->where('level', 'like', 'g4%') // 11
            ->orWhere('level', 'like', 'g3%');
        })->where('updated_at', '>=', Carbon::createFromFormat('Y-m-d H:i:s', '2017-07-06 04:49:00'))
            ->get();

        foreach ($pedigrees as $pedigree) {
            $this->fill_pedigree_from_old($pedigree);

            list($gen, $type) = explode('.', $pedigree->level);
            if ($gen == 'g3') {
                if ($pedigree->rabbit_breeder_id) {
                    $pedigree_father = Pedigree::where('rabbit_breeder_id', $pedigree->rabbit_breeder_id)->
                    where('level', AdminPedigreesController::getFatherLevel($pedigree->level))->first();
                    $pedigree_mother = Pedigree::where('rabbit_breeder_id', $pedigree->rabbit_breeder_id)->
                    where('level', AdminPedigreesController::getMotherLevel($pedigree->level))->first();

                    if ($pedigree_father)
                        $this->fill_pedigree_from_old($pedigree_father);
                    if ($pedigree_mother)
                        $this->fill_pedigree_from_old($pedigree_mother);
                } else if ($pedigree->rabbit_kit_id) {
                    $pedigree_father = Pedigree::where('rabbit_kit_id', $pedigree->rabbit_kit_id)->
                    where('level', AdminPedigreesController::getFatherLevel($pedigree->level))->first();
                    $pedigree_mother = Pedigree::where('rabbit_kit_id', $pedigree->rabbit_kit_id)->
                    where('level', AdminPedigreesController::getMotherLevel($pedigree->level))->first();
                    if ($pedigree_father)
                        $this->fill_pedigree_from_old($pedigree_father);
                    if ($pedigree_mother)
                        $this->fill_pedigree_from_old($pedigree_mother);
                }
            }
        }
    }

    function fill_pedigree_from_old($pedigree)
    {
        $old = \DB::table('hutch_barntrax_7-6.pedigrees')->where('id', $pedigree->id)->first();
        if ($old) {
            $this->comment($pedigree->name . ' => ' . $old->name);
            $pedigree->fill([
                'prefix' => $old->prefix,
                'name' => $old->name,
                'custom_id' => $old->custom_id,
                'color' => $old->color,
                'weight' => $old->weight,
                'breed' => $old->breed,
                'sex' => $old->sex,
                'image' => $old->image,
                'legs' => $old->legs,
                'registration_number' => $old->registration_number,
                'champion_number' => $old->champion_number,
                'rabbit_breeders_id' => $old->rabbit_breeders_id,
                'notes' => $old->notes,
            ]);

            if ($old->day_of_birth) {
                $pedigree->day_of_birth = Carbon::createFromFormat('Y-m-d', $old->day_of_birth)->format(User::getDateFormatPHPSafe());
            } else {
                $pedigree->day_of_birth = null;
            }
            if ($old->aquired) {
                $pedigree->aquired = Carbon::createFromFormat('Y-m-d', $old->aquired)->format(User::getDateFormatPHPSafe());
            } else {
                $pedigree->aquired = null;
            }

            $pedigree->save();
        } else {
            // replace with blank data
            $this->comment($pedigree->name . ' => make this empty');
            $pedigree->fill([
                'prefix' => null,
                'name' => null,
                'custom_id' => null,
                'color' => null,
                'weight' => null,
                'breed' => null,
                'sex' => null,
                'image' => null,
                'legs' => null,
                'registration_number' => null,
                'champion_number' => null,
                'rabbit_breeders_id' => null,
                'notes' => null,
            ]);
            $pedigree->save();
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Models\RabbitBreeder;
use App\Models\RabbitKit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Collective\Bus\Dispatcher;
use App\Models\Pedigree;
use App\Http\Requests\UpdatePedigreeRequest;
use App\Jobs\UpdatePedigreeJob;
use Psy\Util\Json;

class AdminPedigreesController extends Controller
{
    //
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * RabbitBreedersController constructor.
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->middleware('protect', ['only' => ['update', 'destroy','archive','show','getLitters','events']]);
    }

    public function show(Pedigree $pedigree)
    {
        //$breeder->load('father', 'mother','user');
        // dd($pedigree->toArray());
        return $pedigree;
    }

    /**
     * @param Pedigree $pedigree
     * @param UpdatePedigreeRequest $request
     * @return mixed
     */
    public function update(Pedigree $pedigree, UpdatePedigreeRequest $request)
    {
        $request['pedigree'] = $pedigree;
        $updatedPedigree = $this->dispatcher->dispatchFrom(UpdatePedigreeJob::class, $request);
        // return $updatedPedigree;
        // die;
        if($updatedPedigree->rabbit_breeders_id) {
            if ($pedigree->level == "me") {
                $this->set_value_parent($updatedPedigree, 1, 'f1', $updatedPedigree->rabbit_breeders_id);
            } else {
                list($g, $type) = explode('.', $updatedPedigree->level);
                $this->set_value_parent($updatedPedigree, $g[1], $type, $updatedPedigree->rabbit_breeders_id);
            }
        }

        // Breeder pedigree
        if ($updatedPedigree->rabbit_breeder_id) {
            // Update all pedigrees includes me
            $pendingPedigrees = Pedigree::where('rabbit_breeders_id', $updatedPedigree->rabbit_breeder_id)->get();

            foreach ($pendingPedigrees as $pendingPedigree)
            {
                if ($pendingPedigree->level != "me") {
                    $levels = explode('.', $pendingPedigree->level);
                    // Update other pedigrees that includes me
                    AdminPedigreesController::set_value_parent($pendingPedigree, $levels[0][1], $levels[1], $updatedPedigree->rabbit_breeder_id);
                }
            }
        }

        return $updatedPedigree;
    }

    // g: 2
    // type: f1
    // rabbit_breeders_id:
    public static function set_value_parent($pedigree, $g, $type, $rabbit_breeders_id)
    {

        $breeder = null;
        $father = $mother = null;
        if ($rabbit_breeders_id)
            $breeder = RabbitBreeder::where('id',$rabbit_breeders_id)->first();

        // Get father and mother of the breeder
        if($breeder)
        {
            $father = RabbitBreeder::where('id',$breeder->father_id)->first();
            $mother = RabbitBreeder::where('id',$breeder->mother_id)->first();
        }

        $pedigree_father = $pedigree_mother = null;

        if($type[0] == 'f')
        {
            $newtype = $type[1] * 2 - 1;
        }
        else
        {
            $newtype = $type[1] * 2;
        }

        if ($pedigree->rabbit_breeder_id) {
            $pedigree_father = Pedigree::where('rabbit_breeder_id', $pedigree->rabbit_breeder_id)->
            where('level', 'g' . ($g + 1) . '.' . 'f' . $newtype)->first();

            $pedigree_mother = Pedigree::where('rabbit_breeder_id', $pedigree->rabbit_breeder_id)->
            where('level', 'g' . ($g + 1) . '.' . 'm' . $newtype)->first();
        }
        else if ($pedigree->rabbit_kit_id)
        {
            $pedigree_father = Pedigree::where('rabbit_kit_id',$pedigree->rabbit_kit_id)->
            where('level','g'.($g+1).'.'.'f'.$newtype)->first();

            $pedigree_mother = Pedigree::where('rabbit_kit_id',$pedigree->rabbit_kit_id)->
            where('level','g'.($g+1).'.'.'m'.$newtype)->first();
        }

        if (!$pedigree_father || !$pedigree_mother)
            return;

        if ($father) {
            $pedigree_father->fill([
                'prefix' => $father->prefix,
                'name' => $father->name,
                'custom_id' => $father->tattoo,
                'day_of_birth' => $father->date_of_birth,
                'aquired' => $father->aquired,
                'color' => $father->color,
                'weight' => $father->weight,
                'breed' => $father->breed,
                'sex' => $father->sex,
                'image' => $father->image['name'],
                'legs' => $father->legs,
                'registration_number' => $father->registration_number,
                'champion_number' => $father->champion_number,
                'rabbit_breeders_id' => $father->id
            ]);
            $pedigree_father->save();
        } else {
            AdminPedigreesController::fill_pedigree_from_another($pedigree_father, 'g2.f1', $rabbit_breeders_id);
        }

        if($mother) {
            $pedigree_mother->fill([
                'prefix' => $mother->prefix,
                'name' => $mother->name,
                'custom_id' => $mother->tattoo,
                'day_of_birth' => $mother->date_of_birth,
                'aquired' => $mother->aquired,
                'color' => $mother->color,
                'weight' => $mother->weight,
                'breed' => $mother->breed,
                'sex' => $mother->sex,
                'image' => $mother->image['name'],
                'legs' => $mother->legs,
                'registration_number' => $mother->registration_number,
                'champion_number' => $mother->champion_number,
                'rabbit_breeders_id' => $mother->id
            ]);
            $pedigree_mother->save();
        } else {
            AdminPedigreesController::fill_pedigree_from_another($pedigree_mother, 'g2.m1', $rabbit_breeders_id);
        }

        if($type[0] == 'f')
        {
            if ($father) {
                AdminPedigreesController::set_value_parent($pedigree_father, $g + 1, 'f' . ($type[1] * 2 - 1), $father->id);
            }

            if ($mother) {
                AdminPedigreesController::set_value_parent($pedigree_mother, $g + 1, 'm' . ($type[1] * 2 - 1), $mother->id);
            }
        }
        if($type[0] == 'm')
        {
            if ($father) {
                AdminPedigreesController::set_value_parent($pedigree_father, $g + 1, 'f' . ($type[1] * 2), $father->id);
            }

            if ($mother) {
                AdminPedigreesController::set_value_parent($pedigree_mother, $g + 1, 'm' . ($type[1] * 2), $mother->id);
            }
        }
    }

    public static function getFatherLevel($level)
    {
        list($gen, $type) = explode('.',$level);
        $gen = $gen[1] + 1;
        $type = ($type[0] == 'm') ? $type[1] * 2 - 1 : $type[1] * 2;
        return 'g' . $gen . '.' . 'f' . $type;
    }

    public static function getMotherLevel($level)
    {
        list($gen, $type) = explode('.',$level);
        $gen = $gen[1] + 1;
        $type = ($type[0] == 'm') ? $type[1] * 2 - 1 : $type[1] * 2;
        return 'g' . $gen . '.' . 'm' . $type;
    }

    public static function fill_pedigree_from_another($target_pedigree, $source_pedigree_level, $source_breeders_id)
    {
        $copy_father = Pedigree::where('rabbit_breeder_id', $source_breeders_id)->
        where('level', $source_pedigree_level)->first();

        if (!$copy_father)
            return;

        $target_pedigree->fill([
            'prefix' => $copy_father->prefix,
            'name' => $copy_father->name,
            'custom_id' => $copy_father->custom_id,
            'day_of_birth' => $copy_father->day_of_birth,
            'aquired' => $copy_father->aquired,
            'color' => $copy_father->color,
            'weight' => $copy_father->weight,
            'breed' => $copy_father->breed,
            'sex' => $copy_father->sex,
            'image' => $copy_father->image['name'],
            'legs' => $copy_father->legs,
            'registration_number' => $copy_father->registration_number,
            'champion_number' => $copy_father->champion_number,
            'rabbit_breeders_id' => $copy_father->rabbit_breeders_id,
        ]);
        $target_pedigree->save();

        // Get father and mother of target pedigree
        $pedigree_mother = null;
        $pedigree_father = null;

        if ($target_pedigree->rabbit_breeder_id) {
            $pedigree_father = Pedigree::where('rabbit_breeder_id', $target_pedigree->rabbit_breeder_id)->
            where('level', AdminPedigreesController::getFatherLevel($target_pedigree->level))->first();
            $pedigree_mother = Pedigree::where('rabbit_breeder_id', $target_pedigree->rabbit_breeder_id)->
            where('level', AdminPedigreesController::getMotherLevel($target_pedigree->level))->first();
        } else if ($target_pedigree->rabbit_kit_id) {
            $pedigree_father = Pedigree::where('rabbit_kit_id', $target_pedigree->rabbit_kit_id)->
            where('level', AdminPedigreesController::getFatherLevel($target_pedigree->level))->first();
            $pedigree_mother = Pedigree::where('rabbit_kit_id', $target_pedigree->rabbit_kit_id)->
            where('level', AdminPedigreesController::getMotherLevel($target_pedigree->level))->first();
        }

        if (!$pedigree_mother || !$pedigree_father)
            return;

        AdminPedigreesController::fill_pedigree_from_another($pedigree_father, AdminPedigreesController::getFatherLevel($source_pedigree_level), $source_breeders_id);
        AdminPedigreesController::fill_pedigree_from_another($pedigree_mother, AdminPedigreesController::getMotherLevel($source_pedigree_level), $source_breeders_id);
    }

    public function copyOptions(Request $request)
    {
        if (!($user = $request->user())) {
            return response()->json(['options' => []]);
        }
        /* @var $user \App\Models\User */
        $collection = $request->query('type', 'breeder') == 'breeder' ? $user->breeders() : $user->rabbitKits();
        if ($request->query('archived') != 'true') {
            if ($request->query('type', 'breeder') == 'breeder') {
                $collection = $collection->active();
            } else {
                $collection->whereNull('sold_at')->where('alive', 1)->where('archived', 0);
            }
        }
        $collection->where('id', '<>', $request->query('exclude'));
        return response()->json(['options' => $collection->get()->map(function (Model $model) {
            if ($model instanceof RabbitBreeder) {
                return ['id' => $model->id, 'title' => $model->name . ': ' . $model->tattoo];
            }
            if ($model instanceof RabbitKit) {
                return ['id' => $model->id, 'title' => $model->given_id];
            }
        })]);
    }

    public function copy(Request $request)
    {
        $from = $request->request->get('type', 'breeder') == 'breeder'
            ? RabbitBreeder::findOrFail($request->request->get('from'))
            : RabbitKit::findOrFail($request->request->get('from'));
        if($request->get('type', 'breeder') == 'breeder') {
            $pedi = Pedigree::where('id',$request->request->get('to'))->select('rabbit_breeder_id')->first();
            $to = $request->request->get('type', 'breeder') == 'breeder'
                ? RabbitBreeder::findOrFail($pedi->rabbit_breeder_id)
                : RabbitKit::findOrFail($request->request->get('to'));
        } else {
            $to = $request->request->get('type', 'breeder') == 'breeder'
                ? RabbitBreeder::findOrFail($request->request->get('to'))
                : RabbitKit::findOrFail($request->request->get('to'));
        }

        if ($to->user->id != $request->user()->id) {
            return response('Unauthorized', 403);
        }

        /* @var RabbitBreeder|RabbitKit $from */
        /* @var RabbitBreeder|RabbitKit $to */

        switch ($request->request->get('line', 'both')) {
            case 'father':
                $condition = function ($query) {
                    /* @var $query Builder|QueryBuilder */
                    $query->whereIn('level', ['g2.f1', 'g3.f1', 'g3.m1', 'g4.f1', 'g4.m1', 'g4.f2', 'g4.m2']);
                };
                break;
            case 'mother':
                $condition = function ($query) {
                    /* @var $query Builder|QueryBuilder */
                    $query->whereIn('level', ['g2.m1', 'g3.f2', 'g3.m2', 'g4.f3', 'g4.m3', 'g4.f4', 'g4.m4']);
                };
                break;
            case 'both':
            default:
                $condition = function ($query) {
                    /* @var $query Builder|QueryBuilder */
                    $query->whereIn('level', ['g2.f1', 'g3.f1', 'g3.m1', 'g4.f1', 'g4.m1', 'g4.f2', 'g4.m2','g2.m1', 'g3.f2', 'g3.m2', 'g4.f3', 'g4.m3', 'g4.f4', 'g4.m4']);
                };
        }

        $pedigrees = $from->pedigrees();
        $pedigrees->when($condition, $condition);
        
        \DB::statement("CREATE TEMPORARY TABLE `temp_table` {$pedigrees->toSql()}", $pedigrees->getBindings());

        if($request->request->get('type', 'breeder') == 'breeder') {
            \DB::table('temp_table')->update(['rabbit_breeder_id' => $to->id]);
        } else {
            \DB::table('temp_table')->update(['rabbit_kit_id' => $to->id]);
        }
        \Schema::table('temp_table', function (Blueprint $table) {
            $table->dropColumn('id');
        });
        $data = \DB::select("SELECT NULL AS `id`, `temp_table`.* FROM `temp_table`");
        $toPedis = $to->pedigrees();
        $toPedis->when($condition, $condition);

        if($toPedis->count()>0)  $toPedis->delete();

        $data = json_decode(json_encode($data), true);
        $toPedis->insert($data);

        // \DB::insert("INSERT INTO `pedigrees` SELECT NULL AS `id`, `temp_table`.* FROM `temp_table`");
    }
}

<?php

namespace App\Console\Commands;

use App\Models\CageCardTemplatesDefault;
use App\Models\CageCardTemplate;
use App\Models\User;
use Illuminate\Console\Command;

class CreateDefaultCardTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:default-cards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default card templates for all users';

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
        $defaulttemplates = CageCardTemplatesDefault::all();
        $users = User::all();
        foreach ($defaulttemplates as $defaulttemplate)
        {
            foreach ($users as $user)
            {
                $template = new CageCardTemplate;
                $template->name = $defaulttemplate->name;
                $template->type = $defaulttemplate->type;
                $template->size = $defaulttemplate->size;
                $template->hole = $defaulttemplate->hole;
                $template->orientation = $defaulttemplate->orientation;
                $template->fields = $defaulttemplate->fields;
                $template->user_id = $user->id;
                $template->save();
            }
        }
    }
}

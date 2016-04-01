<?php namespace Sugar\Likeable\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Sugar\Likeable\Models\Like;

class CleanCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'likeable:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Remove old likes from the database";

    protected $config;

    public function __construct() {
        $this->config = config('likeable');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire() {
        $threshold = Carbon::now()->subMinutes($this->config['lifetime']);

        $likes = Like::where('created_at', '<=', $threshold);
        if($this->config['clean_only_session_likes']) {
            $likes->where('session_like', true);
        }

        $count = $likes->count();
        foreach ($likes->get() as $like) {
            $like->delete();
        }

        $this->info("Removed ".$count." outdated likes");
    }
}

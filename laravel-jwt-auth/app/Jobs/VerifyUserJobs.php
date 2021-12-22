<?php

namespace App\Jobs;

use App\Mail\VerifyUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;


class VerifyUserJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $details;

    public $timeout = 7200; // 2 hours

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {

        $this->details = $details;



    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $data =  new VerifyUser($this->details);
        Mail::to($this->details['email'])->send($data);



    }
}

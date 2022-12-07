<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Recruiter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Orchestra\Parser\Xml\Facade as XmlParser;

class ImportJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Jobs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $xml = XmlParser::load('https://www.recruitwell.com/wp-content/uploads/doccafe.xml');
        $jobs = [];
        foreach ($xml->getContent() as $xml) {
            $jobs[] = $this->simplexml2array($xml);
        }
        $recruiter = Recruiter::where('email', 'alamb@recruitwell.com')->value('id');
        if (!$recruiter) {
            $recruiter = DB::table('recruiter')->insertGetId([
                'name' => 'Recruitwell',
                'email' => 'alamb@recruitwell.com',
                'num' => '+920000000000',
                'num_code' => '+92',
                'password' => 'qwerty',
                'premium' => 1,
                'company_name' => 'Recruitwell',
                'verified' => 1,
                'deleted' => 0,
            ]);
        }
        foreach ($jobs as $job) {
            $city = City::where('name', $job['city'])->first();
            if ($city) {
                $jobData = [
                    'external_id' => $job['jobnumber'],
                    'job_title' => $job['jobtitle'],
                    'job_description' => $job['jobdescription'],
                    'recruiter_id' => $recruiter,
                    'country_id' => $city->country_id,
                    'city_id' => $city->id,
                    'state_id' => $city->state_id,
                    'salary_type' => 1,
                    'salary_min' => 100,
                    'salary_max' => 10000,
                    'gender' => 'Both',
                    'currency' => 'PKR',
                    'experience_req' => 1,
                    'work_level' => 1,
                    'international_hiring_status' => 0,
                    'inter_hiring_country' => NULL,
                    'status' => 1,
                    'vacancy' => 1,
                    'is_admin_approved' => 0,
                    'job_type' => 'recruitwell',
                ];
                DB::table('job')->updateOrInsert(['external_id' => $job['jobnumber']], $jobData);
            }
        }

        return 1;
    }

    function simplexml2array($xml) {
        if(is_object($xml) && get_class($xml) == 'SimpleXMLElement') {
            $attributes = $xml->attributes();
            foreach($attributes as $k=>$v) {
                $a[$k] = (string) $v;
            }
            $x = $xml;
            $xml = get_object_vars($xml);
        }

        if(is_array($xml)) {
            if(count($xml) == 0) {
                return (string) $x;
            }
            $r = array();
            foreach($xml as $key=>$value) {
                $r[$key] = $this->simplexml2array($value);
            }
            // Ignore attributes
            if (isset($a)) {
                $r['@attributes'] = $a;
            }
            return $r;
        }
        return (string) $xml;
    }

}

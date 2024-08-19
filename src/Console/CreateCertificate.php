<?php

namespace CodeGreenCreative\SamlIdp\Console;

use Illuminate\Console\Command;

class CreateCertificate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'samlidp:cert
                            {--days=7300 : Number of days to add from today as the expiration date}
                            {--subject= : Subj input for OpenSSL request command}
                            {--keyname=key.pem : Full name of the certificate key file}
                            {--certname=cert.pem : Full name to the certificate file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new certificate and private key for your IdP';

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
        $storagePath = storage_path('samlidp');
        $days = $this->option('days');
        $keyname = $this->option('keyname');
        $certname = $this->option('certname');
        $subject = $this->option('subject');

        // Create storage/samlidp directory
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $key = sprintf('%s/%s', $storagePath, $keyname);
        $cert = sprintf('%s/%s', $storagePath, $certname);
        if ($this->canCreateFiles($key, $cert)) {
            $command = 'openssl req -x509 -sha256 -nodes -days %s -newkey rsa:2048 -keyout %s -out %s';
            if ($subject) {
                $command .= ' -subj "' . $subject . '"';
            }

            exec(sprintf($command, $days, $key, $cert));
        }
    }

    protected function canCreateFiles($key, $cert)
    {
        $question = 'The name chosen for the PEM files already exist. Would you like to overwrite existing PEM files?';
        $canCreate = true;
        // If either file exists
        if (file_exists($key) || file_exists($cert)) {
            // Throw input question to user
            $canCreate = $this->confirm($question);
        }
        return $canCreate;
    }
}

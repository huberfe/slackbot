<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 19/12/2017
 * Time: 16:53
 */

namespace Warlof\Seat\Slackbot\Commands;


use Seat\Eveapi\Helpers\JobPayloadContainer;
use Seat\Eveapi\Models\JobTracking;
use Seat\Services\Settings\Seat;

trait JobManager {

	/**
	 * Adds a Job to the queue only if one does not
	 * already exist.
	 *
	 * @param $job
	 * @param $args
	 *
	 * @return mixed
	 */
	public function addUniqueJob($job, JobPayloadContainer $args)
	{

		// Refuse to pop a job onto the queue if the admin
		// has not yet configured an administrative contact.
		// See: https://github.com/eveseat/seat/issues/77 (Request by CCP)
		if ($this->hasDefaultAdminContact()) {

			logger()->error(
				'Default admin contact still set. Not queuing job for: ' . $args->api .
				'Please see: http://seat-docs.readthedocs.io/en/latest/admin_guides/eveapi_admin_contact/');

			return 'Failed to queue due to default config. Please see: ' .
			       'http://seat-docs.readthedocs.io/en/latest/admin_guides/eveapi_admin_contact/';
		}

		// Look for an existing job
		$job_id = JobTracking::where('owner_id', $args->owner_id)
		                     ->where('api', $args->api)
							 ->where('scope', $args->scope)
		                     ->whereIn('status', ['Queued', 'Working'])
		                     ->value('job_id');

		// Just return if the job already exists
		if ($job_id) {

			logger()->warning('A job for Api ' . $args->api . ' - Scope ' . $args-scope . ' and owner ' .
			                  $args->owner_id . ' already exists.');

			return $job_id;
		}

		// Add a new job onto the queue with a delay.
		// The original priority is preserved.
		// The delay grants us a small grace period to use to
		// write a job tracking entry to the database.
		$new_job = (new $job($args))
			->onQueue($args->queue)
			->delay(carbon()->addSeconds(3));

		// Pop the job onto the redis queue. This returns the internal
		// job_id that we use to reference in the tracking table.
		$job_id = dispatch($new_job);

		// Check that the id we got back is a random
		// string and not 0. In fact, normal job_ids
		// are like a 32char string, so just check that
		// its more than 2. If its not, we can assume
		// the job itself was not sucesfully added.
		// If it actually is queued, it will get discarded
		// when trackOrDismiss() is called.
		if (strlen($job_id) < 2) {

			logger()->error('A job was dispatched but the job id returned ' .
			                'was in valid. The jobid was: ' . $job_id);

			return;
		}

		// ...and add tracking information
		JobTracking::create([
			'job_id'   => $job_id,
			'owner_id' => $args->owner_id,
			'api'      => $args->api,
			'scope'    => $args->scope,
			'status'   => 'Queued',
		]);

		return $job_id;

	}

	/**
	 * Checks if the administrative contact has been
	 * configured.
	 *
	 * @return bool
	 */
	public function hasDefaultAdminContact()
	{

		if (Seat::get('admin_contact') === 'seatadmin@localhost.local')
			return true;

		return false;
	}

}
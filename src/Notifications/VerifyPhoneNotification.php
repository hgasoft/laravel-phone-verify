<?php
namespace Takdeniz\PhoneVerify\Notifications;

use Config;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Takdeniz\PhoneVerify\Contracts\MustVerifyPhoneContract;
use Takdeniz\PhoneVerify\Verifier;
use TarfinLabs\Netgsm\Sms\NetgsmOtpMessage;

/**
 * Class VerifyPhoneNotification
 *
 * @package Takdeniz\PhoneVerify\Notifications
 * @author  Tuncay Akdeniz <akdeniztuncay44@gmail.com>
 * @version 0.1
 * @since   0.1
 */
class VerifyPhoneNotification extends Notification implements ShouldQueue
{
	use Queueable;

	/**
	 * @var
	 */
	protected $verifyRequestId;

	/**
	 * Get the notification's channels.
	 *
	 * @param mixed $notifiable
	 * @return array|string
	 * @throws Exception
	 */
	public function via($notifiable)
	{
//		return [NetGsmChannel::class];

		$driver = Verifier::driverResolver($notifiable->getPhoneForVerification());

		return [Verifier::getDriver($driver)->channel()];
	}

	/**
	 * @return mixed
	 */
	protected function getBrand()
	{
		return Config::get('app.name');
	}

	/**
	 * Get the array representation of the notification.
	 *
	 * @param MustVerifyPhoneContract $notifiable
	 * @return array
	 */
	public function toArray($notifiable)
	{
		return [
			'number' => $notifiable->getPhoneForVerification(),
			'brand'  => $this->getBrand(),
		];
	}

	/**
	 * @param MustVerifyPhoneContract $notifiable
	 * @return NetgsmOtpMessage
	 */
	public function toNetgsm($notifiable)
	{
		$driver = Verifier::driverResolver($notifiable->getPhoneForVerification());
		$verification = Verifier::getDriver($driver)->buildVerifyRequest($notifiable);

		$message = trans('verify::verify.send_message', [
			'brand'  => $this->getBrand(),
			'code'   => $verification->code,
			'expire' => 5
		]);

		return new NetGsmOtpMessage($message);
	}
}

<?php
namespace Avatar4eg\UsersList\Api\Controller;

use Flarum\Core\Access\AssertPermissionTrait;
use Flarum\Core\Repository\UserRepository;
use Flarum\Forum\UrlGenerator;
use Flarum\Http\Controller\ControllerInterface;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Message;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zend\Diactoros\Response\EmptyResponse;

class SendAdminEmailController implements ControllerInterface
{
    use AssertPermissionTrait;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var \Flarum\Core\Repository\UserRepository
     */
    protected $users;

    /**
     * @param \Flarum\Settings\SettingsRepositoryInterface $settings
     * @param Mailer $mailer
     * @param TranslatorInterface $translator
     * @param \Flarum\Core\Repository\UserRepository $users
     */
    public function __construct(SettingsRepositoryInterface $settings, Mailer $mailer, TranslatorInterface $translator, UserRepository $users)
    {
        $this->settings = $settings;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->users = $users;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function handle(ServerRequestInterface $request)
    {
        $actor = $request->getAttribute('actor');

        if ($actor !== null && $actor->isAdmin()) {
            $data = array_get($request->getParsedBody(), 'data', []);

            if ($data['forAll']) {
                $users = $this->users->query()->whereVisibleTo($actor)->get();
                foreach ($users as $user) {
                    $email = $user->email;
                    $this->mailer->raw($data['text'], function (Message $message) use ($email, $data) {
                        $message->to($email);
                        $message->subject('[' . $this->settings->get('forum_title') . '] ' . $data['subject'] !== '' ? $data['subject'] : $this->translator->trans('avatar4eg-users-list.email.default_subject'));
                    });
                }
            } else {
                foreach ($data['emails'] as $email) {
                    $this->mailer->raw($data['text'], function (Message $message) use ($email, $data) {
                        $message->to($email);
                        $message->subject('[' . $this->settings->get('forum_title') . '] ' . $data['subject'] !== '' ? $data['subject'] : $this->translator->trans('avatar4eg-users-list.email.default_subject'));
                    });
                }
            }
        }

        return new EmptyResponse;
    }
}

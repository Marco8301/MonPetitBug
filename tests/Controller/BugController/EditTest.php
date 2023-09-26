<?php

namespace App\Tests\Controller\BugController;

use App\DataFixtures\UserFixtures;
use App\Repository\ApplicationRepository;
use App\Tests\Controller\AbstractControllerTest;
use App\Tests\Controller\TestFormInterface;
use App\Tests\Controller\TestRouteInterface;
use App\Tests\Factory\BugFactory;

class EditTest extends AbstractControllerTest implements TestRouteInterface, TestFormInterface
{
    private const URL = '/bugs/%s/edit';
    private const FORM_VALUES = [
        'bug[title]' => 'Title',
        'bug[content]' => 'content',
    ];

    /** @dataProvider provideTestRoute */
    public function testRoute(string $url, int $expectedStatusCode, string $userEmail = null, string $expectedRedirect = null, string $method = 'GET'): void
    {
        $bug = BugFactory::randomOrCreate(['draft' => false])->object();
        $url = sprintf($url, $bug->getId());
        $this->assertRoute($url, $expectedStatusCode, $userEmail, $expectedRedirect, $method);
    }

    public function provideTestRoute(): \Generator
    {
        yield 'Should redirect to login when not connected' => [self::URL, 302, null, 'http://localhost/login'];
        yield 'Should return 403 when connected as user' => [self::URL, 403, UserFixtures::USER_MAIL];
        yield 'Should return 403 when connected as team member' => [self::URL, 403, UserFixtures::TEAM_USER_MAIL];
        yield 'Should return 200 when connected as tech team member' => [self::URL, 200, UserFixtures::TECH_TEAM_USER_MAIL];
        yield 'Should return 200 when connected as admin' => [self::URL, 200, UserFixtures::ADMIN_USER_MAIL];
    }

    /**  @dataProvider provideTestFormIsValid */
    public function testFormIsValid(string $url, string $formSubmit, array $values, ?string $email, ?string $redirectUrl): void
    {
        $bug = BugFactory::randomOrCreate()->object();
        $url = sprintf($url, $bug->getId());
        $this->assertFormIsValid($url, $formSubmit, $values, $email, $redirectUrl);
    }

    public function provideTestFormIsValid(): \Generator
    {
        $values = self::FORM_VALUES;
        $values['bug[application]'] = $this->getContainer()->get(ApplicationRepository::class)->findAll()[0]->getId();
        yield 'Page should redirect to list when form is valid' => [
            self::URL,
            'Mettre à jour',
            $values,
            UserFixtures::TECH_TEAM_USER_MAIL,
            '/bugs/list',
        ];
    }

    /**
     * @param array<string, string> $values
     * @param array<array>          $errors
     *
     * @dataProvider provideTestFormIsNotValid
     */
    public function testFormIsNotValid(string $url, string $route, string $formSubmit, array $values, array $errors, ?string $email, string $alternateSelector = null): void
    {
        $bug = BugFactory::randomOrCreate()->object();
        $url = sprintf($url, $bug->getId());
        $this->assertFormIsNotValid($url, $route, $formSubmit, $values, $errors, $email, $alternateSelector);
    }

    public function provideTestFormIsNotValid(): \Generator
    {
        $values = self::FORM_VALUES;
        $values['bug[application]'] = $this->getContainer()->get(ApplicationRepository::class)->findAll()[0]->getId();
        $values['bug[title]'] = null;
        yield 'Should return an error when title is empty' => [
            self::URL,
            'bug_edit',
            'Mettre à jour',
            $values,
            [
                [
                    'message' => 'This value should not be blank.',
                    'params' => [],
                ],
            ],
            UserFixtures::TECH_TEAM_USER_MAIL,
        ];

        $values = self::FORM_VALUES;
        $values['bug[application]'] = $this->getContainer()->get(ApplicationRepository::class)->findAll()[0]->getId();
        $values['bug[content]'] = null;
        yield 'Should return an error when content is empty' => [
            self::URL,
            'bug_edit',
            'Mettre à jour',
            $values,
            [
                [
                    'message' => 'This value should not be blank.',
                    'params' => [],
                ],
            ],
            UserFixtures::TECH_TEAM_USER_MAIL,
        ];

        $values = self::FORM_VALUES;
        $values['bug[application]'] = '';
        yield 'Should return an error when application is empty' => [
            self::URL,
            'bug_edit',
            'Mettre à jour',
            $values,
            [
                [
                    'message' => 'This value should not be null.',
                    'params' => [],
                ],
            ],
            UserFixtures::TECH_TEAM_USER_MAIL,
        ];
    }
}

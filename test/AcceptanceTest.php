<?php

class AcceptanceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Swift_Message[]
     */
    private $messagesSent = [];

    /**
     * @var BirthdayService
     */
    private $service;

    public function setUp()
    {
        $this->service = new BirthdayService(
            new CsvEmployeeRepository(__DIR__ . '/resources/employee_data.txt'),
            new CallbackMessenger(function ($sender, $subject, $body, $recipient) {
                $msg = Swift_Message::newInstance($subject);
                $msg
                    ->setFrom($sender)
                    ->setTo([$recipient])
                    ->setBody($body)
                ;

                $this->messagesSent[] = $msg;
            })
        );
    }

    public function tearDown()
    {
        $this->service = $this->messagesSent = null;
    }

    /**
     * @test
     */
    public function willSendGreetings_whenItsSomebodysBirthday()
    {
        $this->service->sendGreetings(new XDate('2008/10/08'));

        $this->assertCount(1, $this->messagesSent, 'message not sent?');
        $message = $this->messagesSent[0];
        $this->assertEquals('Happy Birthday, dear John!', $message->getBody());
        $this->assertEquals('Happy Birthday!', $message->getSubject());
        $this->assertCount(1, $message->getTo());
        $this->assertEquals('john.doe@foobar.com', array_keys($message->getTo())[0]);
    }

    /**
     * @test
     */
    public function willNotSendEmailsWhenNobodysBirthday()
    {
        $this->service->sendGreetings(new XDate('2008/01/01'));

        $this->assertCount(0, $this->messagesSent, 'what? messages?');
    }
}

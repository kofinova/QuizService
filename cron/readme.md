Usage
--------------
Cron files handle part of the service functionality, that needs automating.
Files are expected to be outside of public directory.


chooseWinners.php - Task is run weekly and it picks the weekly winners from the quiz players

clearNonverfiedUsers.php - Task is run hourly and deletes every user that has not verified their account 24 hours after creating it.

renewSubscripptions.php - Task is run three times every day and it tries to renew user subscriptions. On the third fail, the account is deactivated.
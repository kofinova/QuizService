# QuizService
- Handles QuizAPI calls.
- Renews user subscriptions if they have enough balance
- Picks weekly winners and awards them


Statistics DB tables
--------------

stats_monetary - gets updated every time user tops up their balance or renews subscription

stats_subscription - gets updated every time a subscription plan is renewed or deactivated

stats_users - gets updated every time a user registers or verifies their account

Statistics about currently active users, number of played questions, correct/incorrect answers can be obtained from the main tables.  
<br/><br/><br/>
**Functionality should be upgraded with validation of input data.*

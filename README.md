# Amazon Alexa Skill: Seizure Tracker

This repository is for development of an Amazon Alexa skill that can be used on Amazon Echo and Echo Dot devices to track epileptic seizures via [SeizureTracker.com](https://www.seizuretracker.com).

The Amazon Alexa Skill can be enabled at [https://www.amazon.com/Seizure-Tracker/dp/B074HBVHRJ/](https://www.amazon.com/Seizure-Tracker/dp/B074HBVHRJ/) and requires a (free) SeizureTracker.com account!

---

### Details

When a request is made to the Seizure Tracker Alexa skill, JSON HTTPS requests are POSTed to [seizure.php](seizure.php).
The request is then usually forwarded to the `handle_seizure` PHP function within [seizure.events.php](seizure.events.php), if it is a valid request for one of the three primary functions of the skill.
There are three primary functions within [seizure.events.php](seizure.events.php) (which is called by [seizure.php](seizure.php)) that interact with the SeizureTracker.com API to do the following things via Alexa voice commands:

  * Count seizures that have occurred today
    - Alexa intent: `CountSeizures`
    - PHP function: `count_seizures`

  * Track a new seizure
    - Alexa intent `AddSeizure`
    - PHP function: `add_seizure`

  * Mark a (previously tracked) seizure as having ended
    - Alexa intent: `EndSeizure`
      PHP function: `end_seizure`

### Account linking

Linking an Amazon Alexa account to a SeizureTracker.com account is done within [login.php](login.php) where OAuth is used.

---

## Future Plans?

#### Including these capabilities could be useful:
  * Track whether seizure medication was taken
  * Track vagal nerve stimulator (VNS) usage

#### "Flash Briefing" ideas:
  * Medication reminder
  * Announce count of seizures for the prior day

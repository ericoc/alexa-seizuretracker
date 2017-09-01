# Amazon Alexa Skill: Seizure Tracker

This repository is for development of an Amazon Alexa skill that can be used on Amazon Echo and Echo Dot devices to track epileptic seizures via [SeizureTracker.com](https://www.seizuretracker.com).

The Amazon Alexa Skill can be enabled at [https://www.amazon.com/Seizure-Tracker/dp/B074HBVHRJ/](https://www.amazon.com/Seizure-Tracker/dp/B074HBVHRJ/) and requires a (free) SeizureTracker.com account!

---

### How does this thing work?

#### Account linking

A users Alexa account/device must be linked to their SeizureTracker.com account in order for the skill to function at all.

Linking an Amazon Alexa account to a SeizureTracker.com account is done within [login.php](login.php) where OAuth is used.

#### Voice commands

When a voice command is made to the Seizure Tracker Alexa skill, Amazon POSTs JSON HTTPS requests to [seizure.php](seizure.php) - which includes the users "intent" as determined by Amazon.

Amazon determines a users intent using a custom JSON configuration defined for the skill by the skill developer. In this case, the JSON configuration for the intent model is defined per [configuration.json](configuration.json).

If a voice command request is valid and for one of the three primary functions of the skill, then the contents of a request is forwarded from [seizure.php](seizure.php) to the `handle_seizure` PHP function within [seizure.events.php](seizure.events.php).

##### Primary functions

There are three primary functions within [seizure.events.php](seizure.events.php) (which is called by [seizure.php](seizure.php)) that interact with the SeizureTracker.com API to do the following things via Alexa voice commands:

| Alexa Intent    | PHP Function      | Purpose                                             |
| --------------- | ----------------- | --------------------------------------------------- |
| `CountSeizures` | `count_seizures`  | Count seizures that have occurred today             |
| `AddSeizure`    | `add_seizure`     | Track a new seizure                                 |
| `EndSeizure`    | `end_seizure`     | Mark a (previously tracked) seizure as having ended |

---

### Future Plans?

#### Including these capabilities could be useful:
  * Track whether seizure medication was taken
  * Track vagal nerve stimulator (VNS) usage

#### "Flash Briefing" ideas:
  * Medication reminder
  * Announce count of seizures for the prior day

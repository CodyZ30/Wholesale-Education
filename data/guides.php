<?php
// Returns an array of 100 fishing guide topics grouped into categories

return [
  'Bass Fishing' => [
    'Topwater Tactics at Dawn',
    'Mastering Spinnerbaits in Murky Water',
    'Swimbaits for Big Largemouth',
    'Dock Skipping with Jigs',
    'Finesse Worms on Pressured Lakes',
    'Seasonal Bass Migration Explained',
    'Electronics 101 for Bass Anglers',
  ],
  'Saltwater Inshore' => [
    'Redfish on the Flats: Sight‑Fishing Basics',
    'Speckled Trout under Birds',
    'Snook at Night around Bridges',
    'Flounder Drifting with Live Bait',
    'Sheepshead on Pilings: Fiddler Crab Rigs',
    'Kayak Inshore Safety and Setup',
  ],
  'Saltwater Offshore' => [
    'Mahi on Weedlines: Trolling Spreads',
    'Yellowfin Tuna Chunking',
    'Wahoo High‑Speed Trolling',
    'Bottom Fishing for Grouper & Snapper',
    'Swordfish Overnight Deep Drops',
    'Offshore Weather & Forecasting',
  ],
  'Fly Fishing' => [
    'Reading a Trout Stream',
    'Euro‑Nymphing for Beginners',
    'Dry Fly Presentation in Clear Water',
    'Streamer Techniques for Big Browns',
    'Saltwater Fly: Bonefish on the Flat',
    'Fly Line Tapers Demystified',
  ],
  'Ice Fishing' => [
    'Heater Safety & Shelter Setup',
    'Jigging Spoons for Walleye',
    'Tip‑Up Strategy for Pike',
    'Electronics on Ice: Flasher Basics',
    'Ice Travel Safety Checklist',
  ],
  'Kayak & Small Craft' => [
    'Kayak Rigging for Stability & Storage',
    'Pedal vs Paddle Drive Pros/Cons',
    'Anchor Systems & Drift Socks',
    'Standing to Sight‑Fish: Balance Drills',
  ],
  'Tackle & Rigging' => [
    'Knots You Can Trust (with Diagrams)',
    'Selecting Leaders for Toothy Fish',
    'Build a Versatile Travel Tackle Kit',
    'Hook Anatomy and Wire Gauge',
    'Color Theory: When It Matters',
  ],
  'Conservation & Ethics' => [
    'Catch & Release Best Practices',
    'Handling Trophy Fish for Photos',
    'Leave‑No‑Trace on Boats & Banks',
    'Understanding Slot Limits',
  ],
  'Seasonal Playbooks' => [
    'Spring Pre‑Spawn Bass Patterns',
    'Summer Mid‑Day Deep Structure',
    'Fall Baitfish Migrations',
    'Winter Slow‑Roll Presentations',
  ],
  'Travel & Destinations' => [
    'Bucket‑List Saltwater Trips',
    'Backcountry Trout Itineraries',
    'DIY Flats Missions on a Budget',
    'What to Pack for a Charter',
  ],
  // Auto‑generated Quick Tips (500 items, no numbers or #)
  'Quick Tips' => (function(){
    $phrases = [
      'Wind‑Driven Bait Corners','Thermocline Clues','Tide Timing Windows','Moon Phase Myths',
      'Bank Fishing Efficiency','Reading Marina Lights','Pier Etiquette','Livewell Care',
      'Handling Hooks Safely','Sharpening Trebles','Trolling Speed Tuning','Planer Boards Basics',
      'Inline vs Snap Weights','Scent Applications','Glide Bait Cadence','Crankbait Deflection',
      'Frog Fishing Mats','Punching Heavy Vegetation','Ned Rig Everywhere','Dropshot Dos and Donts',
      'Swim Jig Around Grass','Umbrella Rig Rules','Jerkbait Pauses','Lipless Over Grass',
      'Chatterbait Trailers','Carolina vs Texas','Free‑Rig Finesse','Hair Jig in Cold',
      'Drifting Sandbars','Bridges on Moving Tide','Jetty Safety','Surf Reading Swells',
      'Wader Safety','Polarized Lenses','Barometer and Bites','Storage Against Rust',
      'DIY Rod Repairs','Reel Maintenance','Line Management','Tackle Box Systems'
    ];
    $qualifiers = [
      'Essentials','Fundamentals','Guide','Playbook','Strategies','Tactics','Approach','Walkthrough',
      'Pro Tips','Advanced','Expert','Mastery','Best Practices','Field Notes','On‑Water Tips','Coach Notes',
      'Checklist','Deep Dive','Primer','Quick Guide'
    ];
    $topics = [];
    foreach ($phrases as $p) {
      foreach ($qualifiers as $q) {
        $topics[] = $p . ' — ' . $q;
        if (count($topics) >= 500) break 2;
      }
    }
    return $topics;
  })(),
];



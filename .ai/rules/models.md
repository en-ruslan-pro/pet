---
paths:
  - app/Models/Character.php
---

# Models

## Character animation selection is clip-based
Character.enabled_animation_clips stores exact GLTF clip names. Null preserves backwards compatibility and means every clip is enabled; newly seeded KayKit characters explicitly include every available clip. TV receives this list and filters its Three.js AnimationMixer clips accordingly.

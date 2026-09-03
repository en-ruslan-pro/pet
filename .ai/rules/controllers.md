---
paths:
  - 'app/Models/Character.php,app/Models/Room.php,app/Http/Controllers/RoomController.php'
---

# Controllers

## Catalog characters remain separate from 3D pet models
Character is the selectable catalog entry for room creation. It stores the display label and default pet name while PetModel remains the reusable 3D asset and action configuration. Rooms keep a nullable character_id and snapshot the effective pet_name for existing-room compatibility.

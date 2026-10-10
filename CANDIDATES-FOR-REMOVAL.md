# Candidates for removal (NOT deleted - needs your approval)

Admin screens that do not match the logistics design (live GPS is handled by your external telematics app).
"APIs kept" means the mobile/backend API routes stay even if the admin screen goes.

| Candidate | Admin routes (routes/admin.php) | Views | Controller | In menu? | Notes |
|---|---|---|---|---|---|
| chat | chat, chat-settings | resources/views/chat | MessagesController, MessageSettingsController | yes (menu-admin) | internal messaging |
| notes | /notes resource, delete-notes | resources/views/notes | Admin/NotesController | yes (menu-ops) | **Backend/NotesApiController + backend.php routes must stay (mobile API)** |
| team | /team resource, delete-team | resources/views/team | Admin/TeamController | yes (menu-admin) | **Backend/TeamApiController stays (API)** |
| twilio | twilio-settings | resources/views/twilio | Admin/TwilioController | yes (menu-admin) | SMS settings. KEEP if you will send SMS expiry alerts (design doc section 21.3) |
| acquisition | /acquisition resource | resources/views/acquisition | Admin/AcquisitionController | no | vehicle purchase info - check vehicles/create still saves purchase data before removing |
| tracker | vehicles-track, traccar-settings | resources/views/tracker | Admin/TrackerController | no | Traccar live tracking; replaced by external telematics |
| geocode / driver_maps / track_driver | driver-maps/, track-driver/{id} | geocode, driver_maps, track_driver .blade.php | DriversController (track_driver, driver_maps methods) | no | live map screens |
| sample / data_index | none (check) | sample.blade.php, data_index.blade.php | Admin/Testcontroller, DatatablesController | no | demo views; controllers reference them but no route found - remove controller + view together |

Safe to delete immediately (no route, no menu, no controller): `resources/views/geocode.blade.php`.

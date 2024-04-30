source: https://htmx.org/docs/
---

#Special Events
htmx provides a few special events for use in hx-trigger:

load - fires once when the element is first loaded
revealed - fires once when an element first scrolls into the viewport
intersect - fires once when an element first intersects the viewport. This supports two additional options:
root:<selector> - a CSS selector of the root element for intersection
threshold:<float> - a floating point number between 0.0 and 1.0, indicating what amount of intersection to fire the event on
You can also use custom events to trigger requests if you have an advanced use case.

#Polling
If you want an element to poll the given URL rather than wait for an event, you can use the every syntax with the hx-trigger attribute:

<div hx-get="/news" hx-trigger="every 2s"></div>
This tells htmx

Every 2 seconds, issue a GET to /news and load the response into the div

If you want to stop polling from a server response you can respond with the HTTP response code 286 and the element will cancel the polling.

---

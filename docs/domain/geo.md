## Geo Section

The geo section answers where the scene is: authors, groups and demoparties
placed on a map of the world.

### What is on the map
The three kinds are independent layers, and each can be switched off. A layer's
switch also carries how many of that kind are in view, and a switched-off layer
is dimmed rather than hidden from the controls.

Zoomed out, the map marks countries. Zoomed in, it marks cities, plus a marker at
the centre of a country for everyone who is placed in that country without a city.
Every marker breaks its count down by kind rather than showing one total.

### Countries and cities as filters
A country or a city is a filter over the map, not a page of its own. Picking a
country fits its cities into view and narrows the lists to it; picking a city
centres on it and narrows the lists further. The choice is part of the address,
so a filtered map can be linked to and shared, and the browser's back and
forward buttons move through it.

Country and city links anywhere on the site open the geo section already
filtered to that place.

### The lists beside the map
Beside the map are the places in view and the entities in view. A place row is
left out when nothing enabled is in it. An entity row names the entity and where
it is; a group also names what kind of group it is, unless that is unknown.

Without a country or city filter the lists follow the map: panning and zooming
changes what they show. With a filter they follow the filter, and panning does
not disturb it.

The lists are paged and searchable.

How it is built: [../features/geo.md](../features/geo.md)

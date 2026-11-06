@extends('layouts.schoolnexa')
@section('title', 'Route Builder')
@section('content')
@include('transport._style')

@php
    /* Existing routes drawn on the map ONLY if their stops are eager-loaded
       (e.g. Route::with('stops')->withCount('stops')) and have latitude/longitude columns. */
    $routeGeo = $routes->getCollection()->map(function ($r) {
        if (!$r->relationLoaded('stops')) return null;
        $pts = $r->stops->map(fn ($s) => [
            'lat'  => data_get($s, 'latitude',  data_get($s, 'lat')),
            'lng'  => data_get($s, 'longitude', data_get($s, 'lng')),
            'name' => data_get($s, 'name', data_get($s, 'stop_name')),
        ])->filter(fn ($p) => is_numeric($p['lat']) && is_numeric($p['lng']))->values();
        return $pts->count() ? ['id' => $r->id, 'name' => $r->name, 'stops' => $pts] : null;
    })->filter()->values();
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
 .trm-layout{display:grid;grid-template-columns:minmax(0,340px) minmax(0,1fr);gap:18px;margin-top:12px;align-items:start}
 @@media (max-width:980px){.trm-layout{grid-template-columns:1fr}}
 .trm-side{display:flex;flex-direction:column;gap:12px}
 .trm-mapwrap{position:relative}
 #routeMap{height:420px;border-radius:14px;border:1px solid #CBD5E1;z-index:0}
 .trm-bar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
 .trm-bar .tr-input{flex:1;min-width:180px}
 .trm-btn{border:1px solid #CBD5E1;background:#fff;border-radius:8px;padding:8px 12px;font-size:13px;font-weight:600;color:#123B5D;cursor:pointer}
 .trm-btn:hover{border-color:#176B87;background:#F1F7FA}
 .trm-btn.danger{color:#991b1b}.trm-btn.danger:hover{background:#fee2e2;border-color:#fca5a5}
 .trm-hint{font-size:12px;color:#64748B;margin:8px 0 0}
 .trm-stats{display:flex;gap:14px;flex-wrap:wrap;margin-top:10px;font-size:13px;color:#25313C}
 .trm-stats b{color:#123B5D}
 .trm-stops{list-style:none;margin:10px 0 0;padding:0;max-height:210px;overflow:auto}
 .trm-stops li{display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #EEF2F6}
 .trm-stops li:last-child{border-bottom:0}
 .trm-stops input{flex:1;min-width:0;border:1px solid #d9dee7;border-radius:6px;padding:5px 8px;font-size:13px}
 .trm-stops button{border:0;background:#fee2e2;color:#991b1b;border-radius:6px;width:26px;height:26px;cursor:pointer;font-weight:700}
 .trm-pin{display:grid;place-items:center;width:28px;height:28px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:#176B87;border:2px solid #fff;box-shadow:0 3px 8px rgba(0,0,0,.35)}
 .trm-pin span{transform:rotate(45deg);color:#fff;font-size:12px;font-weight:700}
 .trm-pin.first{background:#16A34A}.trm-pin.last{background:#DC2626}
 .trm-status{font-size:12px;color:#64748B;margin-left:auto;align-self:center}
 .trm-all{margin-top:12px}
</style>

<div class="tr-wrap">
 <div class="tr-head">
  <div><div class="tr-title">Route Builder</div><div class="tr-sub">Create transport routes and configure stops</div></div>
  <a class="tr-btn" href="{{ route('transport.dashboard') }}">Back to transport</a>
 </div>

 {{-- CREATE ROUTE + MAP --}}
 <div class="tr-card">
  <b>Create route</b>
  <form method="POST" action="{{ route('transport.routes.store') }}" id="routeForm">
   @csrf
   <input type="hidden" name="stops_json" id="stopsJson">
   <div class="trm-layout">

    <div class="trm-side">
     <div class="tr-form">
      <div class="tr-field"><label>Route name</label><input class="tr-input" name="name" required></div>
      <div class="tr-field"><label>Route code</label><input class="tr-input" name="code"></div>
      <div class="tr-field"><label>Distance (km)</label><input class="tr-input" type="number" step=".01" min="0" name="distance_km" id="distanceInput"></div>
      <div class="tr-field"><label>Default fee (₹)</label><input class="tr-input" type="number" step=".01" min="0" name="default_fee"></div>
     </div>

     <div>
      <b style="font-size:13px">Stops <span class="tr-muted" id="stopCount">(0)</span></b>
      <ul class="trm-stops" id="stopList"></ul>
      <p class="tr-muted" id="noStops" style="font-size:12px;margin:6px 0 0">Click on the map to add pickup/drop stops in order.</p>
     </div>

     <button class="tr-btn tr-primary" style="margin-top:4px">Create route</button>
    </div>

    <div class="trm-mapwrap">
     <div class="trm-bar">
      <input type="search" class="tr-input" id="placeSearch" placeholder="Search a place or address…" autocomplete="off">
      <button type="button" class="trm-btn" id="btnSearch">Search</button>
      <button type="button" class="trm-btn" id="btnLocate">My location</button>
      <button type="button" class="trm-btn" id="btnUndo">Undo</button>
      <button type="button" class="trm-btn danger" id="btnClear">Clear</button>
     </div>
     <div id="routeMap"></div>
     <div class="trm-stats">
      <span>Stops: <b id="statStops">0</b></span>
      <span>Road distance: <b id="statDist">—</b></span>
      <span class="trm-status" id="mapStatus"></span>
     </div>
     <p class="trm-hint">Click the map to add a stop. Drag a pin to move it. The distance field fills in automatically (you can still edit it).</p>
    </div>

   </div>
  </form>
 </div>

 {{-- ROUTES TABLE --}}
 <div class="tr-card">
  <b>Transport routes</b>
  <table class="tr-table">
   <thead><tr><th>Route</th><th>Code</th><th>Distance</th><th>Default fee</th><th>Stops</th><th></th></tr></thead>
   <tbody>
   @forelse($routes as $r)
    <tr>
     <td><b>{{ $r->name }}</b></td>
     <td>{{ $r->code ?? '—' }}</td>
     <td>{{ $r->distance_km ?? '—' }} km</td>
     <td>₹{{ number_format($r->default_fee, 2) }}</td>
     <td>{{ $r->stops_count }}</td>
     <td><a class="tr-btn" href="{{ route('transport.routes.show', $r) }}">Build route</a></td>
    </tr>
   @empty
    <tr><td colspan="6" class="tr-muted">No routes configured.</td></tr>
   @endforelse
   </tbody>
  </table>
  <div style="margin-top:12px">{{ $routes->links() }}</div>
 </div>

 @if($routeGeo->count())
  <div class="tr-card trm-all">
   <b>All routes on map</b>
   <div id="allMap" style="height:340px;border-radius:14px;border:1px solid #CBD5E1;margin-top:10px;z-index:0"></div>
  </div>
 @endif
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function(){
 var $ = function(s){return document.querySelector(s)};
 if(typeof L==='undefined'){$('#mapStatus').textContent='Map library failed to load.';return;}

 var tiles='https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
 var attr='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

 var map=L.map('routeMap').setView([18.5204,73.8567],12); // default view; use search / My location to move
 L.tileLayer(tiles,{maxZoom:19,attribution:attr}).addTo(map);

 var stops=[];          // {marker, name}
 var line=null,reqId=0,timer=null;

 function pin(n,total){
  var cls='trm-pin'+(n===1?' first':'')+(n===total&&total>1?' last':'');
  return L.divIcon({className:'',html:'<div class="'+cls+'"><span>'+n+'</span></div>',iconSize:[28,28],iconAnchor:[14,28]});
 }
 function status(t){$('#mapStatus').textContent=t||''}

 function addStop(latlng,name){
  var m=L.marker(latlng,{draggable:true}).addTo(map);
  var s={marker:m,name:name||''};
  m.on('dragend',function(){refresh()});
  stops.push(s);refresh();
 }

 function refresh(){
  // re-number icons
  stops.forEach(function(s,i){s.marker.setIcon(pin(i+1,stops.length))});
  // list
  var ul=$('#stopList');ul.innerHTML='';
  stops.forEach(function(s,i){
   var li=document.createElement('li');
   var inp=document.createElement('input');inp.value=s.name||'';inp.placeholder='Stop '+(i+1)+' name';
   inp.addEventListener('input',function(){s.name=inp.value;sync()});
   var x=document.createElement('button');x.type='button';x.textContent='×';x.title='Remove';
   x.addEventListener('click',function(){map.removeLayer(s.marker);stops.splice(stops.indexOf(s),1);refresh()});
   var n=document.createElement('span');n.textContent=(i+1)+'.';n.style.cssText='font-weight:700;color:#176B87;width:20px';
   li.appendChild(n);li.appendChild(inp);li.appendChild(x);ul.appendChild(li);
  });
  $('#stopCount').textContent='('+stops.length+')';
  $('#statStops').textContent=stops.length;
  $('#noStops').style.display=stops.length?'none':'block';
  sync();drawRoute();
 }

 function sync(){
  $('#stopsJson').value=JSON.stringify(stops.map(function(s,i){
   var p=s.marker.getLatLng();
   return {order:i+1,name:s.name||('Stop '+(i+1)),lat:+p.lat.toFixed(6),lng:+p.lng.toFixed(6)};
  }));
 }

 function haversine(a,b){
  var R=6371,t=Math.PI/180,dLat=(b.lat-a.lat)*t,dLng=(b.lng-a.lng)*t;
  var h=Math.sin(dLat/2)*Math.sin(dLat/2)+Math.cos(a.lat*t)*Math.cos(b.lat*t)*Math.sin(dLng/2)*Math.sin(dLng/2);
  return 2*R*Math.asin(Math.sqrt(h));
 }

 function setDistance(km){
  $('#statDist').textContent=km?km.toFixed(2)+' km':'—';
  if(km){$('#distanceInput').value=km.toFixed(2)}
 }

 function drawRoute(){
  clearTimeout(timer);
  if(line){map.removeLayer(line);line=null}
  var pts=stops.map(function(s){return s.marker.getLatLng()});
  if(pts.length<2){setDistance(0);status('');if(pts.length===1)map.panTo(pts[0]);return}

  // straight-line preview immediately, then replace with the road route
  line=L.polyline(pts,{color:'#176B87',weight:4,opacity:.6,dashArray:'8 8'}).addTo(map);
  var straight=0;for(var i=1;i<pts.length;i++)straight+=haversine(pts[i-1],pts[i]);
  setDistance(straight);status('Fetching road route…');

  var id=++reqId;
  timer=setTimeout(function(){
   var coords=pts.map(function(p){return p.lng+','+p.lat}).join(';');
   fetch('https://router.project-osrm.org/route/v1/driving/'+coords+'?overview=full&geometries=geojson')
    .then(function(r){return r.json()})
    .then(function(d){
     if(id!==reqId)return;
     if(!d.routes||!d.routes.length)throw new Error('no route');
     if(line)map.removeLayer(line);
     line=L.geoJSON(d.routes[0].geometry,{style:{color:'#176B87',weight:5,opacity:.9}}).addTo(map);
     setDistance(d.routes[0].distance/1000);status('Road route ready');
     map.fitBounds(line.getBounds(),{padding:[40,40]});
    })
    .catch(function(){if(id===reqId)status('Road route unavailable — showing straight-line distance')});
  },450);
 }

 map.on('click',function(e){addStop(e.latlng)});

 $('#btnUndo').addEventListener('click',function(){
  var s=stops.pop();if(s){map.removeLayer(s.marker);refresh()}
 });
 $('#btnClear').addEventListener('click',function(){
  stops.forEach(function(s){map.removeLayer(s.marker)});stops=[];refresh();
 });

 // Place search (OpenStreetMap Nominatim)
 function search(){
  var q=$('#placeSearch').value.trim();if(!q)return;
  status('Searching…');
  fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q='+encodeURIComponent(q))
   .then(function(r){return r.json()})
   .then(function(d){
    if(!d.length){status('Place not found');return}
    map.setView([+d[0].lat,+d[0].lon],15);status(d[0].display_name.split(',').slice(0,2).join(','));
   }).catch(function(){status('Search failed')});
 }
 $('#btnSearch').addEventListener('click',search);
 $('#placeSearch').addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();search()}});

 $('#btnLocate').addEventListener('click',function(){
  if(!navigator.geolocation){status('Location not supported');return}
  status('Locating…');
  navigator.geolocation.getCurrentPosition(function(p){
   map.setView([p.coords.latitude,p.coords.longitude],15);status('');
  },function(){status('Location permission denied')});
 });

 // Don't let Enter in the search box submit the form
 $('#routeForm').addEventListener('submit',function(){sync()});

 /* ---- Optional: all existing routes on one map ---- */
 var geo=@json($routeGeo);
 if(geo.length && document.getElementById('allMap')){
  var all=L.map('allMap');L.tileLayer(tiles,{maxZoom:19,attribution:attr}).addTo(all);
  var colors=['#176B87','#16A34A','#D97706','#5B3FA3','#DC2626','#3B82F6'],bounds=[];
  geo.forEach(function(r,i){
   var pts=r.stops.map(function(s){return [+s.lat,+s.lng]}),c=colors[i%colors.length];
   L.polyline(pts,{color:c,weight:4,opacity:.85}).addTo(all).bindTooltip(r.name,{sticky:true});
   r.stops.forEach(function(s,j){L.circleMarker([+s.lat,+s.lng],{radius:5,color:'#fff',weight:2,fillColor:c,fillOpacity:1}).addTo(all)
    .bindPopup('<b>'+r.name+'</b><br>'+(s.name||('Stop '+(j+1))))});
   bounds=bounds.concat(pts);
  });
  all.fitBounds(bounds,{padding:[30,30]});
 }
})();
</script>
@endsection
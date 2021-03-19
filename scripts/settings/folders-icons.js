var MplusIcons=function(t){function L(L){return t.has(d,L)&&d[L].size?[void 0!==d[L].origin?d[L].origin:0,d[L].size]:[0,r]}function C(t,L){return`<symbol viewBox="0 0 ${r} ${r}" id="${a}${t}"><path d="${L}"></path></symbol>`}function i(L,C,i){var l=[],n=void 0!==C.origin?C.origin:0;return t.each(C.paths||[],(function(L){var C=t.has(L,"color")?` fill=${L.color}`:"",i=t.has(L,"className")?` class=${L.className}`:"",n=t.has(L,"id")?` id=${L.id}`:"";l.push(`<path d="${L.d}"${n}${C}${i}></path>`)})),i?t.join(l,""):`<symbol viewBox="${n} ${n} ${C.size} ${C.size}" id="${a}${L}">\n\t\t\t\t${t.join(l,"")}\n\t\t\t</symbol>`}function l(){var L=[];return t.each(d||[],(function(l,n){L.push(t.isString(l)?C(n,l):i(n,l))})),`<svg style="display:none;" xmlns="http://www.w3.org/2000/svg">${t.join(L,"")}</svg>`}function n(){var L='viewBox="0,0,600,495"',C={};return t.each(s,(function(i,l){C[l]=`<svg xmlns="http://www.w3.org/2000/svg" ${L}><g>${t.trim(i).replace(/>\s+</gm,"><")}</g></svg>`})),C}function e(C,i){if(!t.has(!0===i?d:h,C))return"";var l=L(C),n=!0===i?`<use href="#${a}${C}"></use>`:h[C]();return`<svg\n\t\t\t\tclass="${a}svg ${a}${C}"\n\t\t\t\trole="img"\n\t\t\t\taria-labelledby="title"\n\t\t\t\tviewBox="${l[0]} ${l[0]} ${l[1]} ${l[1]}"\n\t\t\t\tpreserveAspectRatio="xMidYMin slice"\n\t\t\t>\n\t\t\t\t${n}\n\t\t\t</svg>`}function o(t){return e(t,!0)}var a="mfs-",r=24,d={plus:"M17,12.981 L17,11 L13,11 L13,7 L11,7 L11,11 L7,11 L7,13 L11,13 L11,17 L13,17 L13,13 L17,13 z M17.433,5.002 Q18.053,5.002 18.526,5.475 Q19,5.949 19,6.569 L19,17.433 Q19,18.053 18.526,18.526 Q18.053,19 17.433,19 L6.568,19 Q5.912,19 5.456,18.544 Q5,18.088 5,17.432 L5,6.568 Q5,5.912 5.456,5.456 Q5.912,5 6.568,5 L17.433,5 z",minus:"M17,13 L17,11 L7,11 L7,13 L17,13 z M17.433,5.002 Q18.053,5.002 18.526,5.475 Q19,5.949 19,6.569 L19,17.433 Q19,18.053 18.526,18.526 Q18.053,19 17.433,19 L6.568,19 Q5.912,19 5.456,18.544 Q5,18.088 5,17.432 L5,6.568 Q5,5.912 5.456,5.456 Q5.912,5 6.568,5 L17.433,5 z",closed:"M9.984 3.984l2.016 2.016h8.016q0.797 0 1.383 0.609t0.586 1.406v9.984q0 0.797-0.586 1.406t-1.383 0.609h-16.031q-0.797 0-1.383-0.609t-0.586-1.406v-12q0-0.797 0.586-1.406t1.383-0.609h6z",opened:"M20.016 18v-9.984h-16.031v9.984h16.031zM20.016 6q0.797 0 1.383 0.609t0.586 1.406v9.984q0 0.797-0.586 1.406t-1.383 0.609h-16.031q-0.797 0-1.383-0.609t-0.586-1.406v-12q0-0.797 0.586-1.406t1.383-0.609h6l2.016 2.016h8.016z",home:{size:80,paths:[{id:"border",d:"M55.996,35.996 C63.996,35.996 67.996,59.996 67.996,59.996 L11.996,59.996 C11.996,59.996 16,43.996 23.999,43.996 C32.002,43.996 31.998,47.996 37.998,47.996 C43.998,47.996 47.997,35.996 55.996,35.996 z M55.996,39.996 C53.476,39.996 51.113,42.584 48.605,45.332 C45.761,48.456 42.546,51.996 37.994,51.996 C34.139,51.996 31.902,50.708 30.115,49.676 C28.495,48.736 27.219,47.996 23.995,47.996 C21.767,47.996 19.284,51.864 17.547,56 L63.112,56 C61.076,47.212 57.797,40.2 55.996,39.996 z M26,23.996 C29.308,23.996 32,26.688 32,29.996 C32,33.304 29.308,35.996 26,35.996 C22.692,35.996 20,33.304 20,29.996 C20,26.688 22.692,23.996 26,23.996 z M26,19.996 L26,19.996 C20.476,19.996 16,24.472 16,29.996 C16,35.52 20.476,39.996 26,39.996 C31.524,39.996 36,35.52 36,29.996 C36,24.472 31.524,19.996 26,19.996 z"},{id:"sunset",d:"M26.022,38.702 C21.225,38.702 17.335,34.813 17.335,30.016 C17.335,25.218 21.225,21.329 26.022,21.329 C30.819,21.329 34.708,25.218 34.708,30.016 C34.708,34.813 30.819,38.702 26.022,38.702 z M56.973,38.702 C54.211,38.702 51.635,40.03 48.886,43.395 C45.769,47.221 42.693,50.883 37.704,50.883 C33.477,50.883 31.376,49.151 29.417,47.888 C27.641,46.737 26.161,46.69 22.627,46.69 C20.185,46.69 16.957,51.834 15.054,56.898 L64.994,56.898 C62.763,46.137 58.946,39.052 56.973,38.802",color:"#DA2C41",className:"accent"},{id:"frame",d:"M71.999,8 C76.416,8 80,11.584 80,16 L80,64.001 C80,68.417 76.416,72 71.999,72 L8,72 C3.584,72 0,68.417 -0,64.001 L-0,16 C0,11.584 3.584,8 8,8 L71.999,8 z M72.012,16 L8,16 L8,64.001 L72,64.001 L72.012,16 z"}]},close:{origin:-2,size:24,paths:[{d:"M14.95 6.46L11.41 10l3.54 3.54-1.41 1.41L10 11.42l-3.53 3.53-1.42-1.42L8.58 10 5.05 6.47l1.42-1.42L10 8.58l3.54-3.53z"}]}},s={zu:'<path d="M300,32.352 L300,24.141 C300,10.808 289.191,-0 275.859,-0 L24.141,-0 C10.809,-0 0,10.808 0,24.141 L0,459.451 L600,459.451 L600,56.493 C600,43.16 589.191,32.352 575.859,32.352 L300,32.352" fill="#FBE36F" id="back"/>\n\t\t<path d="M26.636,67.138 L578.972,67.138 L578.972,463.866 L26.636,463.866 z" fill="#FFFFFF" id="paper"/>\n\t\t<path d="M291.443,114.664 L282.536,139.358 L24.141,139.358 C10.809,139.358 0,150.166 0,163.499 L0,470.859 C0,484.192 10.809,495 24.141,495 L575.859,495 C589.191,495 600,484.192 600,470.859 L600,122.855 C600,109.522 589.191,98.714 575.859,98.714 L314.152,98.714 C303.978,98.714 294.895,105.093 291.443,114.664" fill="#F6D33D" id="cover"/>\n\t\t<path d="M600,470.859 L600,270.468 C433.343,432.574 144.274,475.949 6.712,487.534 C11.106,492.126 17.283,495 24.141,495 L575.858,495 C589.191,495 600,484.191 600,470.859" fill="#F1C73F" id="dark"/>\n\t\t<path d="M243.654,158.788 L39.632,158.788 C28.866,158.788 20.139,167.516 20.139,178.282 L20.139,221.462 C54.457,197.951 131.896,168.335 243.654,158.788" fill="#F8DC47" id="light"/>',mac:'<path d="M88.022,-0 C66.716,-0 58.037,9.476 58.037,30.793 L58.037,30.793 C58.037,50.775 35.858,43.707 33.073,72.642 L33.073,72.642 L567.644,72.642 C566.73,56.015 560.414,47.376 540.169,47.376 L540.169,47.376 L278.983,47.376 C270.305,47.376 256.888,45.794 256.888,32.372 L256.888,32.372 C256.888,10.262 249.786,-0 225.327,-0 L225.327,-0 z" fill="#F1C73F" id="dark"/>\n\t\t<path d="M30.724,72.642 C6.649,72.642 -1.448,87.693 0.215,106.072 L0.215,106.072 C0.215,106.072 7.735,188.725 10.205,238.461 L10.205,238.461 C12.658,288.205 15.341,461.014 15.341,461.014 L15.341,461.014 C15.341,492.231 14.935,494.768 49.661,494.768 L49.661,494.768 L551.3,495 C584.067,495 585.068,495.168 585.068,461.61 L585.068,461.61 C585.068,461.61 585.559,331.953 590.182,258.589 L590.182,258.589 C594.806,185.251 599.875,106.153 599.875,106.153 L599.875,106.153 C601.049,87.713 593.718,72.642 568.473,72.642 L568.473,72.642 z" fill="#F6D33C" id="cover"/>\n\t\t<path d="M504.449,72.642 C385.243,87.638 268.682,120.878 157.637,166.424 C107.025,187.184 57.347,210.512 10.197,238.311 C7.744,194.173 4.205,150.096 0.215,106.072 C-1.448,87.694 6.648,72.642 30.723,72.642 L504.449,72.642 z" fill="#FBE36E" id="light"/>',simple:'<path d="M540,60 L270,60 L210,0 L60,0 C27,0 0,27 0,60 L0,180 L600,180 L600,120 C600,87 573,60 540,60 z" fill="#F1C73F" id="dark"/>\n\t\t<path d="M600,470.859 C600,484.192 589.191,495 575.859,495 L540.004,495 L540,495 L60,495 L59.996,495 L24.141,495 C10.809,495 0,484.192 0,470.859 L0,120 C0,87 27,60 60,60 L540,60 C573,60 600,87 600,120 L600,470.859 z" fill="#F6D33C" id="cover"/>\n\t\t<path d="M402,60 C401.333,60 402,60 402,60 L60,70 C32.523,70 10,92.523 10,120 L0,420 L0,420 L0,120 C0,87 27,60 60,60 L402,60 z" fill="#FFFFFF" fill-opacity="0.5" id="border"/>',open:'<path d="M566.381,49.868 L290.862,49.868 C290.862,49.868 280.043,34.578 272.124,23.53 C264.206,12.484 257.07,-0 238.504,-0 L141.634,-0 C123.068,-0 108.015,4.965 108.015,23.53 L108.015,49.868 L95.703,49.868 C77.135,49.868 62.083,64.92 62.083,83.483 L62.083,461.383 C62.083,479.948 77.135,495 95.703,495 L566.381,495 C584.947,495 599.999,479.948 599.999,461.383 L599.999,83.483 C599.999,64.92 584.947,49.868 566.381,49.868" fill="#F1C73F" id="dark"/>\n\t\t<path d="M92.342,74.518 L569.744,74.518 L569.744,477.343 L92.342,477.343 z" fill="#FFFFFE" id="paper"/>\n\t\t<path d="M0.424,131.602 C-2.518,115.237 10.153,101.972 28.718,101.972 L499.398,101.972 C517.964,101.972 535.402,115.237 538.343,131.602 L599.576,465.368 C602.518,481.731 589.849,495 571.282,495 L100.604,495 C82.037,495 64.601,481.731 61.659,465.368 L0.424,131.602" fill="#F6D33C" id="cover"/>',pack:'<path d="M292.165,35.218 L292.165,14.277 C292.165,6.425 285.736,0 277.879,0 L64.07,0 C56.213,0 49.784,6.425 49.784,14.277 L49.784,35.218 C49.784,35.218 32.15,35.218 27.174,35.218 C22.199,35.218 13.73,41.643 13.594,49.494 L14.553,449.728 C14.417,457.578 20.736,464.014 28.593,464.02 L574.44,464.02 C582.297,464.028 588.644,457.625 588.547,449.775 L586.401,49.494 C586.304,41.641 579.797,35.218 571.94,35.218 L292.165,35.218 z" fill="#F1C73F" id="dark"/>\n\t\t<path d="M557.493,457.86 L42.504,457.86 L41.076,52.489 L558.921,52.489 z" fill="#EAEAEA" id="paper"/>\n\t\t<path d="M557.493,461.86 L42.504,461.86 L32.504,75.05 L567.493,75.05 z" fill="#D5D5D5" id="paper-dark"/>\n\t\t<path d="M587.87,480.755 C587.733,488.608 581.196,495.019 573.338,495 L27.49,495 C19.633,494.98 12.259,487.607 12.123,479.758 L-0,114.274 C-0.136,106.423 6.181,100 14.039,100 L585.958,100 C593.816,100 600.133,106.425 599.996,114.276 L587.87,480.755 z" fill="#F6D33C" id="cover"/>'},h={home:function(){return i("home",d.home,!0)},close:function(){return i("close",d.close,!0)}};return{collection:l,svg:e,icon:o,folders:n()}};const FolderIcons=MplusIcons;export default FolderIcons;
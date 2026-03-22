#!/usr/bin/env node

'use strict';

const fs = require('fs');
const path = require('path');

const outputDir = path.resolve(__dirname, '../../htdocs/js/ng-zxart');
const indexPath = path.join(outputDir, 'index.html');
const manifestPath = path.join(outputDir, 'manifest.json');

const html = fs.readFileSync(indexPath, 'utf8');

/** Returns true for local Angular bundle filenames (no protocol, no leading slash) */
function isLocalBundle(src) {
    return !src.includes('://') && !src.startsWith('//') && !src.startsWith('/');
}

const scripts = [];
const stylesSet = new Set();

const scriptRegex = /<script[^>]+src="([^"]+)"[^>]*>/g;
let match;
while ((match = scriptRegex.exec(html)) !== null) {
    if (isLocalBundle(match[1])) {
        scripts.push(match[1]);
    }
}

const styleRegex = /<link[^>]+href="([^"]+)"[^>]*>/g;
while ((match = styleRegex.exec(html)) !== null) {
    const href = match[1];
    if (isLocalBundle(href) && href.endsWith('.css')) {
        stylesSet.add(href);
    }
}

const manifest = { scripts, styles: [...stylesSet] };
fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 2));

console.log('manifest.json generated:', manifest);

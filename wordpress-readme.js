// eslint-disable-next-line no-undef
const marked = require('marked');
// eslint-disable-next-line no-undef
const fs = require('fs');

const readmeData = {
    contributors: 'dmitryrudakov',
    tags: 'gutenberg, folders, dominant color, admin, media library folders, media library',
    tested: '5.6.2',
    license: 'GPLv2 or later',
};

// Skip tokens
const skipTokens = [
    // if 'contains' is true then skip all tokens of this 'type'
    { type:'html', contains: true },
    { type:'blockquote', contains: '&#x1F383;' },
    { type:'paragraph', contains: 'img.shields.io' },
    { type:'paragraph', contains: 'user-images.githubusercontent.com' },
    // { type:'heading', contains: 'Download' },
    // { type:'list', contains: 'archive/master.zip' },
    // { type:'list', contains: 'downloads.wordpress.org' },
];

// Skip sections
const skipSections = [
    'Download',
    'Public API methods',
];

// Skip log record if it ends with these strings
const skipLogs = [
    '(-)',
    '[-]',
];

const options = {
  main: 'zu-media.php',
  from: 'README.md',
  to: 'readme.txt',
  log: 'CHANGES.md',
  limitRecordrs: 15,
};

function readFileAndSaveResult() {

    const buffer = fs.readFileSync(options.from);
    const readme = parseReadme(buffer.toString());

    const buffer2 = fs.readFileSync(options.log);
    const log = parseChangelog(buffer2.toString(), options.limitRecordrs);

    const result = String(readme + '\n\n== Changelog ==\n\n' + log).replace(/\n{3,}/g, '\n\n');
    fs.writeFileSync(options.to, result);
}

function parseReadme(md) {

    const tokens = marked.lexer(md);
    // console.error('Tokens:', tokens);

    let content = [];
    let skipSection = { enabled: false, depth: 0 };
    let screenshots = 0;

    tokens.forEach(token => {

        // skip single tokens
        const shouldSkip = skipTokens.reduce((acc, value) => {
            if(acc === true || value === undefined) return acc;
            if(token.type === value.type &&
                (value.contains === true || token.raw.includes(value.contains))
            ) return true;
            else return false;
        }, false);

        // skip whole sections
        if(token.type === 'heading') {
            if(skipSection.enabled) {
                if(token.depth === skipSection.depth) skipSection.enabled = false;
            } else {
                skipSections.forEach(heading => {
                    if(token.raw.includes(heading)) {
                        skipSection.enabled = true;
                        skipSection.depth = token.depth;
                    }
                });
            }
        }

        if(shouldSkip || skipSection.enabled) return;

        if(token.type === 'heading') {
            // main heading
            if(token.depth === 1) {
                const heading = token.text.split(':')[0] || token.text;
                content.push(`=== ${heading.trim()} ===${parseHeader(readmeData)}\n\n`);
                return;
            }
            // regular headings & screenshots
            if(token.depth === 2) {
                if(token.raw.includes('[![')) {
                    let screenshotName = null;
                    try {
                      screenshotName = token.tokens[0].tokens[0].text;
                    } catch(e) { /* ignore the error */ }

                    if(screenshotName) content.push(`${++screenshots}. ${screenshotName}\n`);
                } else {
                    content.push(`== ${token.text} ==\n\n`);
                }
                return;
            }
        }

        content.push(token.raw);
    });
    return content.join('');
}

function parseChangelog(md, limit) {

    const tokens = marked.lexer(md);
    // console.error('Changelog Tokens:', tokens);
    let content = [];
    let count = 0;

    tokens.forEach(token => {
        if(count > limit) return;

        if(token.type === 'heading') {
            if(token.depth === 4) {
                const heading = token.text.split('/')[0] || token.text;
                content.push(`\n### ${heading.trim()} ###\n`);
                return;
            }
        }

        if(token.type === 'list') {
            token.items.forEach(item => {
                // skip single log record
                const shouldSkip = skipLogs.reduce((acc, value) => {
                    if(acc === true || value === undefined) return acc;
                    if(item.text.endsWith(value)) return true;
                    else return false;
                }, false);

                if(shouldSkip) return;
                else content.push(item.raw);
            });
            count++;
        }
    });
    return content.join('');
}

function parseHeader(data) {

    const buffer = fs.readFileSync(options.main);
    const string = buffer.toString();

    const version = /Version\s*:\s*([^\s]+)/gm.exec(string);
    data.stable = version !== null ? version[1] : '1.0.0';

    const requires = /Requires at least\s*:\s*([^\s]+)/gm.exec(string);
    data.requires = requires !== null ? requires[1] : '5.1';

    const php = /Requires PHP\s*:\s*([^\s]+)/gm.exec(string);
    data.php = php !== null ? php[1] : '7.0';

    return `
Contributors: ${data.contributors}
Tags: ${data.tags}
Requires at least: ${data.requires}
Tested up to: ${data.tested}
Stable tag: ${data.stable}
License: ${data.license}
Requires PHP: ${data.php}`;
}

// read file & process it
readFileAndSaveResult();

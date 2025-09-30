// scripts/generate-structure.js
const fs = require('fs');
const path = require('path');

function walk(dir, depth = 0, maxDepth = 5, ignore = ['node_modules', '.git']) {
  if (maxDepth >= 0 && depth > maxDepth) return '';
  let items = fs.readdirSync(dir, { withFileTypes: true });
  items = items.filter(i => !ignore.includes(i.name));
  let out = '';
  items.forEach((item, idx) => {
    const isLast = idx === items.length - 1;
    const prefix = '│   '.repeat(depth) + (isLast ? '└── ' : '├── ');
    out += prefix + item.name + '\n';
    if (item.isDirectory()) {
      out += walk(path.join(dir, item.name), depth + 1, maxDepth, ignore);
    }
  });
  return out;
}

const root = process.cwd();
const tree = root + '\n' + walk(root, 0, 5); // ajuste maxDepth (5)
fs.writeFileSync('project-structure.md', tree);
console.log('project-structure.md gerado.');

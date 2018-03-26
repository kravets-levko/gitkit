import { each, eachRight, map, extend, last } from 'lodash';

export function prepareDiff(diff) {
  // offsets in diff are 1-based, but lines are 0-based
  return map(diff, item => {
    if (item.delete.offset > 0) {
      item.delete.offset -= 1;
    }
    item.delete.last = item.delete.offset + item.delete.count;

    if (item.add.offset > 0) {
      item.add.offset -= 1;
    }
    item.add.last = item.add.offset + item.add.count;

    return item;
  });
}

export function stringToLines(str, additionalProps) {
  return map((str + '').split('\n'), (value, number) => extend({
    value,
    number: number + 1,
  }, additionalProps));
}

export function applyDiff(str, diff) {
  str = str.split('\n');
  // process rules from end to beginning as they may change count of lines
  eachRight(diff, (range) => {
    // we have final version of file; replace added lines with deleted
    str.splice(range.add.offset, range.add.count, ...range.delete.lines);
  });
  return str.join('\n');
}

export function mergeLines(prev, current, diff) {
  // prepare lines - set linen numbers, mark changed lines
  each(prev, line => { line.numberRemoved = line.number });
  each(current, line => { line.numberAdded = line.number });
  each(diff, range => {
    for (let i = range.delete.offset; i < range.delete.last; i++) {
      prev[i].isRemoved = true;
    }
    for (let i = range.add.offset; i < range.add.last; i++) {
      current[i].isAdded = true;
    }
  });

  // merge
  const result = [];

  let prevCursor = 0;
  let currentCursor = 0;
  let diffCursor = 0;

  // do one extra loop to add rest of lines
  while (diffCursor <= diff.length) {
    // get current range, or "fake" one - at the end of each stream
    const range = diff[diffCursor] || {
      delete: { offset: prev.length, count: 0, last: prev.length },
      add: { offset: current.length, count: 0, last: current.length },
    };
    // move to current range
    while (
      (prevCursor < range.delete.offset) &&
      (currentCursor < range.add.offset)
      ) {
      const prevLine = prev[prevCursor];
      const currentLine = current[currentCursor];
      result.push(extend({}, prevLine, currentLine));
      prevCursor += 1;
      currentCursor += 1;
    }
    // append deletions
    while (prevCursor < range.delete.last) {
      result.push(prev[prevCursor]);
      prevCursor += 1;
    }
    // append additions
    while (currentCursor < range.add.last) {
      result.push(current[currentCursor]);
      currentCursor += 1;
    }

    // move to next range
    diffCursor += 1;
  }

  return result;
}

export function collapseLines(lines, contextLinesCount) {
  const result = [];

  let buffer = [];
  let firstRangeFound = false;
  each(lines, line => {
    if (line.isAdded || line.isRemoved) {
      if (buffer.length > 0) {
        if (buffer.length > contextLinesCount * (firstRangeFound ? 2 : 1)) {
          if (firstRangeFound) {
            result.push(...buffer.splice(0, contextLinesCount));
          }
          result.push({
            isCollapsedBlock: true,
            value: '',
            lines: buffer.splice(0, buffer.length - contextLinesCount),
          });
          result.push(...buffer);
        } else {
          result.push(...buffer);
        }
        buffer = [];
      }
      result.push(line);
      firstRangeFound = true;
    } else {
      buffer.push(line);
    }
  });

  if (buffer.length > 0) {
    result.push(...buffer.splice(0, contextLinesCount));
    if (buffer.length > 0) {
      result.push({
        isCollapsedBlock: true,
        value: '',
        lines: buffer,
      });
    }
  }

  if ((result.length > 0) && !result[0].isCollapsedBlock) {
    // prepend
    result.splice(0, 0, {
      isCollapsedBlock: true,
      value: '',
      lines: [],
    });
  }

  // mark collapsed lines
  each(result, (line, index) => {
    if (line.isCollapsedBlock) {
      each(line.lines, item => item.isCollapsed = true);

      const next = result[index + 1];
      if (next) {
        let addedCount = 0;
        let removedCount = 0;
        for (let i = index + 1; i < result.length; i++) {
          const current = result[i];
          if (current.isCollapsedBlock) {
            break;
          }
          if (current.isAdded) {
            addedCount += 1;
          } else if (current.isRemoved) {
            removedCount += 1;
          } else {
            addedCount += 1;
            removedCount += 1;
          }
        }

        const lineRemoved = next.numberRemoved || 1;
        const lineAdded = next.numberAdded || 1;
        line.value = `@@ -${lineRemoved},${removedCount} +${lineAdded},${addedCount} @@`;
      }
    }
  });

  return result;
}

export function removeTrailingNewline(lines) {
  if ((lines.length > 0) && (last(lines).value === '')) {
    lines.splice(lines.length - 1, 1); // remove
    return true;
  }
  return false;
}

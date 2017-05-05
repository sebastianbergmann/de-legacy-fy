<?php
/*
 * This file is part of de-legacy-fy.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\DeLegacyFy;

class XdebugTraceParser
{
    /**
     * @param string $filename
     * @param string $unit
     *
     * @return array
     */
    public function parse($filename, $unit)
    {
        $data       = [];
        $parameters = null;

        $fh = \fopen($filename, 'r');

        while ($line = \fgets($fh)) {
            $line = \explode("\t", $line);

            if (\strpos($line[0], 'File format') === 0) {
                $columns = \explode(' ', $line[0]);
                $version = \array_pop($columns);

                if ($version < 4) {
                    throw new RuntimeException(
                        'Execution trace data file must be in format version 4 (or later)'
                    );
                }

                continue;
            }

            if (\count($line) == 13 && $line[5] == $unit) {
                $parameters = \array_map('trim', \array_slice($line, 11, $line[10]));
            }

            if ($parameters !== null && \count($line) == 6 && $line[2] == 'R') {
                $data[]     = \array_merge([\trim($line[5])], $parameters);
                $parameters = null;
            }
        }

        \fclose($fh);

        return $data;
    }
}

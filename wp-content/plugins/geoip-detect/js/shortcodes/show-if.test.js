const { getTestRecordError, getTestRecord } = require("../test-lib/test-records");
import { geoip_detect2_shortcode_evaluate_conditions } from './show-if';

const testFixture = require('../../tests/fixture_shortcode_show_if.json');


test.each(testFixture)('Show if Test #%d: %s', (nb, input, expected, parsed, opt) => {
    const record = getTestRecord(opt.lang);
    const result = geoip_detect2_shortcode_evaluate_conditions(parsed, opt, record);
    expect(result).toBe(expected);
});


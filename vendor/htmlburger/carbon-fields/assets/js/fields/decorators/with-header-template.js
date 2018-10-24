/**
 * The external dependencies.
 */
import { connect } from 'react-redux';

/**
 * The internal dependencies.
 */
import { getComplexGroupLabel } from 'fields/selectors';

/**
 * Get values from the store.
 *
 * @param  {Object} state
 * @param  {Object} props
 * @param  {Object} props.group
 * @return {Object}
 */
const mapStateToProps = (state, { group, index }) => ({
	label: getComplexGroupLabel(state, group, index),
});

export default connect(mapStateToProps);

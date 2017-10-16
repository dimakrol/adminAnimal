'use strict';

//export default (...stores) => stores.find(store => store.isAvailable());
export default (...stores) => stores.filter(store => store.isAvailable())[0];
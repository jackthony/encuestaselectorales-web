function candidatosPorDistrito(distritoId, candidatos, partidos) {
  var partidosPorId = {};
  partidos.forEach(function (p) {
    partidosPorId[p.id] = p;
  });

  return candidatos
    .filter(function (c) {
      return c.distritoId === distritoId;
    })
    .map(function (c) {
      var partido = partidosPorId.hasOwnProperty(c.partidoId) ? partidosPorId[c.partidoId] : null;
      return {
        id: c.id,
        nombre: c.nombre,
        numero: c.numero,
        foto: c.foto,
        partido: partido
          ? { nombre: partido.nombre, siglas: partido.siglas, color: partido.color }
          : null,
      };
    })
    .sort(function (a, b) {
      if (a.numero !== b.numero) {
        if (a.numero === null) return 1;
        if (b.numero === null) return -1;
        return a.numero - b.numero;
      }
      return a.nombre.localeCompare(b.nombre, 'es');
    });
}

if (typeof module !== 'undefined' && module.exports) {
  module.exports = candidatosPorDistrito;
}

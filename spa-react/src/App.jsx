import { useState } from 'react';
import { HashRouter, Routes, Route, Link } from 'react-router-dom';

function Carrinho() {
  const [itens, setItens] = useState(() => JSON.parse(localStorage.getItem('carrinho_egg') || '[]'));
  const [msg, setMsg] = useState('');

  const atualizarQtd = (id, delta) => {
    const novos = itens
      .map(i => i.id === id ? { ...i, quantidade: i.quantidade + delta } : i)
      .filter(i => i.quantidade > 0);
    setItens(novos);
    localStorage.setItem('carrinho_egg', JSON.stringify(novos));
  };

  const finalizarCompra = async () => {
    const resp = await fetch('../../api.php?acao=finalizar_pedido', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ itens })
    });
    const dados = await resp.json();
    if (dados.status === 'ok') {
      localStorage.removeItem('carrinho_egg');
      setItens([]);
      setMsg(`Pedido #${dados.pedido_id} registrado com sucesso no banco!`);
    } else {
      setMsg(dados.mensagem);
    }
  };

  const total = itens.reduce((acc, i) => acc + Number(i.preco) * i.quantidade, 0);

  return (
    <div>
      <h3>Meu Carrinho (SPA React)</h3>
      {msg && <div className="alert alert-info">{msg}</div>}
      {itens.length === 0 ? <p>Seu carrinho está vazio.</p> : (
        <>
          <ul className="list-group mb-3">
            {itens.map(i => (
              <li key={i.id} className="list-group-item d-flex justify-content-between align-items-center">
                <span>{i.nome} — R$ {Number(i.preco).toFixed(2)}</span>
                <div>
                  <button className="btn btn-sm btn-outline-secondary me-1" onClick={() => atualizarQtd(i.id, -1)}>-</button>
                  <span className="mx-2">{i.quantidade}</span>
                  <button className="btn btn-sm btn-outline-secondary me-2" onClick={() => atualizarQtd(i.id, 1)}>+</button>
                </div>
              </li>
            ))}
          </ul>
          <h5>Total: R$ {total.toFixed(2)}</h5>
          <button className="btn btn-success" onClick={finalizarCompra}>Finalizar Pedido (Gravar N..N)</button>
        </>
      )}
    </div>
  );
}

function ConsultorIA() {
  const [ocasiao, setOcasiao] = useState('');
  const [resposta, setResposta] = useState('');
  const [carregando, setCarregando] = useState(false);

  const consultar = async (e) => {
    e.preventDefault();
    setCarregando(true);
    setResposta('');
    try {
      const resp = await fetch('../../api.php?acao=recomendar_ia', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ocasiao })
      });
      const textoBruto = await resp.text();
      try {
        const dados = JSON.parse(textoBruto);
        setResposta(dados.resposta || dados.mensagem);
      } catch {
        setResposta('Erro: o servidor não retornou JSON. Resposta recebida: ' + textoBruto.slice(0, 200));
      }
    } catch (erro) {
      setResposta('Falha de rede ao chamar api.php: ' + erro.message);
    } finally {
      setCarregando(false);
    }
  };

  return (
    <div>
      <h3>Consultor de Fragrâncias & Acessórios (Gemini IA)</h3>
      <form onSubmit={consultar} className="mb-3">
        <input
          className="form-control mb-2"
          placeholder="Ex: Jantar romântico à noite com vestido preto..."
          value={ocasiao}
          onChange={e => setOcasiao(e.target.value)}
          required
        />
        <button className="btn btn-dark" disabled={carregando}>
          {carregando ? 'Consultando IA...' : 'Pedir Combinação'}
        </button>
      </form>
      {resposta && <div className="card p-3 bg-light">{resposta}</div>}
    </div>
  );
}

export default function App() {
  return (
    <HashRouter>
      <div className="container py-4">
        <div className="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
          <h4>Essência Girls Girl — Área Interativa React</h4>
          <div className="d-flex gap-2">
            <Link className="btn btn-outline-dark btn-sm" to="/">Carrinho</Link>
            <Link className="btn btn-outline-primary btn-sm" to="/consultor">Consultor IA</Link>
            <a className="btn btn-secondary btn-sm" href="../../index.php">Voltar à Loja</a>
          </div>
        </div>
        <Routes>
          <Route path="/" element={<Carrinho />} />
          <Route path="/consultor" element={<ConsultorIA />} />
        </Routes>
      </div>
    </HashRouter>
  );
}
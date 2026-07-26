'use client';

import React, { useState, useEffect } from 'react';
import { MessageSquare, User, ShoppingCart, HelpCircle, Shield, ExternalLink, RefreshCw } from 'lucide-react';

export default function AgentDashboard() {
  const [conversations, setConversations] = useState([
    { id: '1', customerName: 'Jane Doe', email: 'jane.doe@example.com', status: 'escalated', lastMessage: 'My package is lost...' },
    { id: '2', customerName: 'Guest Shopper', email: 'Guest', status: 'ai_active', lastMessage: 'What is the price of Nitro 5?' }
  ]);
  const [selectedChat, setSelectedChat] = useState<any>(null);
  const [messages, setMessages] = useState<any[]>([]);
  const [inputVal, setInputVal] = useState('');

  const handleSelectChat = (chat: any) => {
    setSelectedChat(chat);
    if (chat.id === '1') {
      setMessages([
        { sender: 'customer', text: 'Where is my order #10000492?' },
        { sender: 'ai', text: 'Your order was shipped via FedEx. Tracking: 1234567890.' },
        { sender: 'customer', text: 'My package says delivered but I don\'t see it. I need to talk to a human.' }
      ]);
    } else {
      setMessages([
        { sender: 'customer', text: 'I need a gaming laptop under ₹70,000.' },
        { sender: 'ai', text: 'I found Nitro 5 Gaming Laptop matching your budget.' }
      ]);
    }
  };

  const handleSend = () => {
    if (!inputVal) return;
    setMessages(prev => [...prev, { sender: 'agent', text: inputVal }]);
    setInputVal('');
  };

  return (
    <div style={{ display: 'flex', height: '100vh', fontFamily: 'sans-serif', backgroundColor: '#f1f3f4' }}>
      {/* Sidebar - Chats list */}
      <div style={{ width: '320px', borderRight: '1px solid #dadce0', display: 'flex', flexDirection: 'column', backgroundColor: '#ffffff' }}>
        <div style={{ padding: '16px', borderBottom: '1px solid #dadce0', display: 'flex', alignItems: 'center', gap: '8px' }}>
          <MessageSquare color="#1a73e8" />
          <h2 style={{ fontSize: '18px', margin: 0, fontWeight: 'bold' }}>Aprilo Live Queue</h2>
        </div>
        <div style={{ flex: 1, overflowY: 'auto' }}>
          {conversations.map((chat) => (
            <div
              key={chat.id}
              onClick={() => handleSelectChat(chat)}
              style={{
                padding: '16px',
                borderBottom: '1px solid #f1f3f4',
                cursor: 'pointer',
                backgroundColor: selectedChat?.id === chat.id ? '#e8f0fe' : '#ffffff'
              }}
            >
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
                <span style={{ fontWeight: 'bold', fontSize: '14px' }}>{chat.customerName}</span>
                <span
                  style={{
                    fontSize: '11px',
                    padding: '2px 6px',
                    borderRadius: '4px',
                    backgroundColor: chat.status === 'escalated' ? '#fce8e6' : '#e6f4ea',
                    color: chat.status === 'escalated' ? '#c5221f' : '#137333'
                  }}
                >
                  {chat.status.toUpperCase()}
                </span>
              </div>
              <p style={{ margin: 0, fontSize: '12px', color: '#5f6368', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                {chat.lastMessage}
              </p>
            </div>
          ))}
        </div>
      </div>

      {/* Main Chat Area */}
      <div style={{ flex: 1, display: 'flex', flexDirection: 'column', height: '100%' }}>
        {selectedChat ? (
          <>
            {/* Header info */}
            <div style={{ padding: '16px', borderBottom: '1px solid #dadce0', backgroundColor: '#ffffff', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <div>
                <h3 style={{ margin: 0, fontSize: '16px', fontWeight: 'bold' }}>{selectedChat.customerName}</h3>
                <span style={{ fontSize: '12px', color: '#5f6368' }}>{selectedChat.email}</span>
              </div>
              <button style={{ padding: '8px 16px', border: 'none', backgroundColor: '#1a73e8', color: '#ffffff', borderRadius: '4px', cursor: 'pointer', fontWeight: 'bold' }}>
                Take Over Chat
              </button>
            </div>

            {/* Chat Log */}
            <div style={{ flex: 1, overflowY: 'auto', padding: '16px', display: 'flex', flexDirection: 'column', gap: '12px' }}>
              {messages.map((msg, index) => (
                <div
                  key={index}
                  style={{
                    alignSelf: msg.sender === 'customer' ? 'flex-start' : 'flex-end',
                    maxWidth: '60%',
                    padding: '12px',
                    borderRadius: '8px',
                    fontSize: '14px',
                    backgroundColor: msg.sender === 'customer' ? '#ffffff' : '#1a73e8',
                    color: msg.sender === 'customer' ? '#202124' : '#ffffff',
                    border: msg.sender === 'customer' ? '1px solid #dadce0' : 'none'
                  }}
                >
                  {msg.text}
                </div>
              ))}
            </div>

            {/* Input area */}
            <div style={{ padding: '16px', borderTop: '1px solid #dadce0', backgroundColor: '#ffffff', display: 'flex', gap: '8px' }}>
              <input
                type="text"
                placeholder="Type reply to customer..."
                value={inputVal}
                onChange={(e) => setInputVal(e.target.value)}
                style={{ flex: 1, padding: '12px', border: '1px solid #dadce0', borderRadius: '4px', outline: 'none' }}
              />
              <button
                onClick={handleSend}
                style={{ padding: '12px 24px', border: 'none', backgroundColor: '#1a73e8', color: '#ffffff', borderRadius: '4px', cursor: 'pointer', fontWeight: 'bold' }}
              >
                Send
              </button>
            </div>
          </>
        ) : (
          <div style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', color: '#5f6368' }}>
            <MessageSquare size={64} style={{ marginBottom: '16px' }} />
            <p>Select a conversation from the sidebar queue to start assisting shoppers.</p>
          </div>
        )}
      </div>

      {/* Customer Insights sidebar */}
      {selectedChat && (
        <div style={{ width: '300px', borderLeft: '1px solid #dadce0', backgroundColor: '#ffffff', padding: '16px', display: 'flex', flexDirection: 'column', gap: '20px' }}>
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', borderBottom: '1px solid #f1f3f4', paddingBottom: '8px', marginBottom: '12px' }}>
              <User size={18} color="#5f6368" />
              <h4 style={{ margin: 0, fontWeight: 'bold', fontSize: '14px' }}>Customer Profile</h4>
            </div>
            <p style={{ margin: '0 0 4px 0', fontSize: '13px' }}><strong>Name:</strong> {selectedChat.customerName}</p>
            <p style={{ margin: '0 0 4px 0', fontSize: '13px' }}><strong>Email:</strong> {selectedChat.email}</p>
            <p style={{ margin: '0 0 4px 0', fontSize: '13px' }}><strong>LTV:</strong> ₹1,24,000</p>
          </div>

          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', borderBottom: '1px solid #f1f3f4', paddingBottom: '8px', marginBottom: '12px' }}>
              <ShoppingCart size={18} color="#5f6368" />
              <h4 style={{ margin: 0, fontWeight: 'bold', fontSize: '14px' }}>Active Shopping Cart</h4>
            </div>
            <p style={{ margin: '0 0 4px 0', fontSize: '13px' }}>No active cart items</p>
          </div>

          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', borderBottom: '1px solid #f1f3f4', paddingBottom: '8px', marginBottom: '12px' }}>
              <Shield size={18} color="#5f6368" />
              <h4 style={{ margin: 0, fontWeight: 'bold', fontSize: '14px' }}>Licensing Status</h4>
            </div>
            <p style={{ margin: '0 0 4px 0', fontSize: '12px', color: '#137333', backgroundColor: '#e6f4ea', padding: '4px 8px', borderRadius: '4px', display: 'inline-block' }}>
              License: ACTIVE
            </p>
          </div>
        </div>
      )}
    </div>
  );
}

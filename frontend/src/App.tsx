import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import "./App.css";

function App() {
  const [count, setCount] = useState(0);

  return (
    <>
      <Button variant="outline" onClick={() => setCount(count + 1)}>
        Count
      </Button>
      <Badge variant="outline">Count: {count}</Badge>
    </>
  );
}

export default App;
